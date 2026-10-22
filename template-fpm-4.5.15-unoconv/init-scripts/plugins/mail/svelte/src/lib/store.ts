/*
 * SPDX-FileCopyrightText: 2023-2024 Proyecto UNIMOODLE <direccion.area.estrategia.digital@uva.es>
 * SPDX-FileCopyrightText: 2024 Albert Gasset <albertgasset@fsfe.org>
 *
 * SPDX-License-Identifier: GPL-3.0-or-later
 */

import { writable } from 'svelte/store';
import {
    callServices,
    type CountMessagesResponse,
    type CreateLabelRequest,
    type CreateMessageRequest,
    type DeleteLabelRequest,
    type EmptyTrashRequest,
    type ForwardMessageRequest,
    type GetCoursesResponse,
    type GetLabelsResponse,
    type GetMessageFormeResponse,
    type GetMessageResponse,
    type MessageQuery,
    type ReplyMessageRequest,
    type SearchMessagesResponse,
    type ServiceRequest,
    type SetDeletedRequest,
    type SetLabelsRequest,
    type SetPreferencesRequest,
    type SetStarredRequest,
    type SetUnreadRequest,
    type UpdateLabelRequest,
} from './services';
import {
    DeletedStatus,
    ViewportSize,
    type Dialog,
    type InitialData,
    type Message,
    type MessageData,
    type MessageForm,
    type MessageSummary,
    type SelectAllType,
    type ServiceError,
    type State,
    type Toast,
    type ViewParams,
    type Preferences,
    type Group,
    type Role,
} from './state';
import { getViewParamsFromUrl, setUrlFromViewParams } from './url';
import { replaceStringParams } from './utils';

export type Store = Awaited<ReturnType<typeof createStore>>;

export async function createStore(data: InitialData) {
    let draftTimeoutId = 0;

    let state: State = {
        /* Info */
        userid: data.userid,
        settings: data.settings,
        preferences: data.preferences,
        strings: data.strings,
        mobile: data.mobile,

        /* URL parameters */
        params: {},

        /* Data */
        unread: 0,
        drafts: 0,
        courses: [],
        labels: [],
        totalCount: 0,
        listMessages: [],

        /* Transient */
        loading: true,
        selectedMessages: new Map(),
        toasts: [],
        navigationId: 0,
        viewportSize: 0,
    };

    const { subscribe, set } = writable<State>(state);

    const patch = (update: Partial<State>) => set({ ...state, ...update });

    subscribe((newState) => {
        state = newState;
    });

    const callServicesAndRefresh = async (
        requests: ServiceRequest[],
        newParams?: ViewParams,
        redirect = false,
    ): Promise<unknown[] | null> => {
        const messageid = state.message?.id;
        const draftData = state.draftData;
        const params = newParams || state.params;
        const prevParams =
            !redirect &&
            (params.tray != state.params.tray ||
                params.messageid != state.params.messageid ||
                params.courseid != state.params.courseid ||
                params.labelid != state.params.labelid ||
                params.search != state.params.search)
                ? { ...state.params, dialog: undefined }
                : state.prevParams;
        const perpage = state.preferences.perpage;

        patch({ loading: true, error: undefined });

        setUrlFromViewParams(params, redirect);

        // Save draft.
        if (messageid && draftData) {
            window.clearTimeout(draftTimeoutId);
            draftTimeoutId = 0;
            requests.unshift({
                methodname: 'update_message',
                messageid,
                data: draftData,
            });
        }

        // Courses.
        requests.push({
            methodname: 'get_courses',
        });

        // Labels.
        requests.push({
            methodname: 'get_labels',
        });

        if (params.tray) {
            const query: MessageQuery = {
                courseid: params.courseid,
                labelid: params.tray == 'label' ? params.labelid : undefined,
                draft: params.tray == 'drafts' ? true : params.tray == 'sent' ? false : undefined,
                roles:
                    params.tray == 'inbox'
                        ? ['to', 'cc', 'bcc']
                        : params.tray == 'sent'
                          ? ['from']
                          : undefined,
                starred: params.tray == 'starred' ? true : undefined,
                deleted: params.tray == 'trash',
            };

            // Total count of messages.
            requests.push({
                methodname: 'count_messages',
                query,
            });

            if (params.messageid) {
                // Full message.
                requests.push({
                    methodname: 'get_message',
                    messageid: params.messageid,
                });

                // Next message.
                requests.push({
                    methodname: 'search_messages',
                    query: {
                        ...query,
                        ...params.search,
                        startid: params.messageid,
                        reverse: false,
                    },
                    limit: 1,
                });

                // Previous message.
                requests.push({
                    methodname: 'search_messages',
                    query: {
                        ...query,
                        ...params.search,
                        startid: params.messageid,
                        reverse: true,
                    },
                    limit: 1,
                });

                if (!params.search) {
                    // Offset of the message.
                    requests.push({
                        methodname: 'count_messages',
                        query: {
                            ...query,
                            startid: params.messageid,
                            reverse: true,
                        },
                    });
                }
            } else {
                // List of messages.
                requests.push({
                    methodname: 'search_messages',
                    query: { ...query, ...params.search },
                    offset: params.search ? undefined : params.offset,
                    limit: params.search ? perpage + 1 : perpage,
                });
            }
        }

        const responses = await callServicesAndSetError(requests);
        if (responses == null) {
            return null;
        }

        let message: Message | undefined;
        let messageOffset: CountMessagesResponse | undefined;
        let nextMessageId: number | undefined;
        let prevMessageId: number | undefined;
        let listMessages: ReadonlyArray<MessageSummary> = [];
        let totalCount: CountMessagesResponse = 0;

        if (params.tray) {
            if (params.messageid) {
                if (!params.search) {
                    messageOffset = responses.pop() as CountMessagesResponse;
                }
                prevMessageId = (responses.pop() as SearchMessagesResponse)[0]?.id;
                nextMessageId = (responses.pop() as SearchMessagesResponse)[0]?.id;
                message = responses.pop() as GetMessageResponse;
            } else {
                listMessages = responses.pop() as SearchMessagesResponse;
                if (params.search) {
                    if (params.search.reverse) {
                        prevMessageId = listMessages[perpage]?.id;
                        nextMessageId = params.search.startid;
                        listMessages = listMessages.slice(0, perpage).reverse();
                    } else {
                        prevMessageId = params.search.startid;
                        nextMessageId = listMessages[perpage]?.id;
                        listMessages = listMessages.slice(0, perpage);
                    }
                }
            }
            totalCount = responses.pop() as CountMessagesResponse;
        }

        const labels = responses.pop() as GetLabelsResponse;
        const courses = responses.pop() as GetCoursesResponse;
        if (messageid && draftData) {
            responses.shift();
        }

        // Check if the course or label exists.
        if (
            (params.tray == 'course' && !courses.find((c) => c.id == params.courseid)) ||
            (params.tray == 'label' && !labels.find((l) => l.id == params.labelid))
        ) {
            await navigate({ tray: 'inbox' }, true);
            return responses;
        }

        // Fetch form if message is a draft.
        let draftForm: MessageForm | undefined;
        let draftRoles: ReadonlyArray<Role> | undefined;
        let draftGroups: ReadonlyArray<Group> | undefined;
        if (message?.draft) {
            const requests: ServiceRequest[] = [
                {
                    methodname: 'get_roles',
                    courseid: message.course.id,
                },
                {
                    methodname: 'get_groups',
                    courseid: message.course.id,
                },
            ];
            if (message?.id != messageid) {
                requests.push({
                    methodname: 'get_message_form',
                    messageid: message.id,
                });
            }
            const responses = await callServicesAndSetError(requests);
            if (responses == null) {
                return null;
            }
            if (message?.id != messageid) {
                draftForm = responses.pop() as GetMessageFormeResponse;
            }
            draftGroups = responses.pop() as ReadonlyArray<Group>;
            draftRoles = responses.pop() as ReadonlyArray<Role>;
        }

        // Update state with fetched data.
        patch({
            params,
            prevParams,
            unread: courses.reduce((total, course) => total + course.unread, 0),
            drafts: courses.reduce((total, course) => total + course.drafts, 0),
            courses,
            labels,
            messageOffset,
            totalCount,
            listMessages,
            message,
            nextMessageId,
            prevMessageId,
            draftForm: message?.id != messageid ? draftForm : state.draftForm,
            draftRoles,
            draftGroups,
            draftData: undefined,
            draftSaved: message?.id == messageid,
            selectedMessages: new Map(
                message
                    ? [[message.id, message]]
                    : state.message
                      ? []
                      : listMessages
                            .filter((message) => state.selectedMessages.has(message.id))
                            .map((message) => [message.id, message]),
            ),
            loading: false,
            // Scroll to top and prevent animations if changing page.
            navigationId:
                redirect ||
                params.tray != state.params.tray ||
                params.courseid != state.params.courseid ||
                params.labelid != state.params.labelid ||
                params.messageid != state.params.messageid ||
                params.offset != state.params.offset
                    ? state.navigationId + 1
                    : state.navigationId,
        });

        // Display draft saved notification if navigated to another page.
        if (messageid && draftData && messageid != message?.id) {
            showToast({ text: state.strings.draftsaved });
        }

        return responses;
    };

    const callServicesAndSetError = async (
        requests: ServiceRequest[],
    ): Promise<unknown[] | null> => {
        try {
            return await callServices(requests);
        } catch (error) {
            setError(error as ServiceError);
            return null;
        }
    };

    const createLabel = async (name: string, color: string) => {
        const request: CreateLabelRequest = {
            methodname: 'create_label',
            name,
            color,
            messageids: Array.from(state.selectedMessages.keys()),
        };

        await callServicesAndRefresh([request]);
    };

    const createMessage = async (courseid: number | undefined) => {
        const oldParams = state.params;

        const request: CreateMessageRequest = {
            methodname: 'create_message',
            courseid: courseid || state.courses[0].id,
        };

        const responses = await callServicesAndSetError([request]);

        if (responses != null) {
            await navigate({
                tray: 'drafts',
                messageid: responses.pop() as number,
                courseid:
                    ['shortname', 'fullname'].includes(state.settings.filterbycourse) &&
                    oldParams.courseid
                        ? courseid
                        : undefined,
            });
        }
    };

    const deleteLabel = async (labelid: number) => {
        const request: DeleteLabelRequest = {
            methodname: 'delete_label',
            labelid,
        };
        await callServicesAndRefresh([request], { tray: 'inbox' });
    };

    const emptyTrash = async (courseid?: number) => {
        const request: EmptyTrashRequest = {
            methodname: 'empty_trash',
            courseid,
        };
        await callServicesAndRefresh([request]);
    };

    const forward = async (message: Message) => {
        const oldParams = state.params;

        const request: ForwardMessageRequest = {
            methodname: 'forward_message',
            messageid: message.id,
        };

        const responses = await callServicesAndRefresh([request]);

        if (responses != null) {
            await navigate({
                tray: 'drafts',
                messageid: responses.pop() as number,
                courseid:
                    ['shortname', 'fullname'].includes(state.settings.filterbycourse) &&
                    oldParams.courseid != null
                        ? message.course.id
                        : undefined,
            });
        }
    };

    const hideDialog = () => {
        const newParams: ViewParams = { ...state.params, dialog: undefined };
        patch({ params: newParams });
        setUrlFromViewParams(newParams, true);
    };

    const hideToast = (toast: Toast) => {
        patch({ toasts: state.toasts.filter((t) => t != toast) });
    };

    const navigate = async (params?: ViewParams, redirect = false, init = true) => {
        const requests: ServiceRequest[] = [];

        if (init && state.settings.incrementalsearch) {
            requests.push({
                methodname: 'search_messages',
                query: { deleted: false },
                offset: state.settings.incrementalsearchlimit,
                limit: 1,
            });
        }

        if (params?.messageid) {
            requests.push({
                methodname: 'view_message',
                messageid: params.messageid,
            });
        }

        const responses = await callServicesAndRefresh(requests, params, redirect);

        if (responses != null && init && state.settings.incrementalsearch) {
            const messages = responses[0] as MessageSummary[];
            patch({ incrementalSearchStopId: messages[0]?.id });
        }
    };

    const navigateToList = async (redirect = false) => {
        const params: ViewParams = {
            ...state.params,
            messageid: undefined,
            offset:
                Math.floor((state.params.offset || 0) / state.preferences.perpage) *
                    state.preferences.perpage || undefined,
        };

        await callServicesAndRefresh([], params, redirect);
    };

    const navigateToMenu = async () => {
        await callServicesAndRefresh([], {});
    };

    const reply = async (message: Message, all: boolean) => {
        const oldParams = state.params;

        const request: ReplyMessageRequest = {
            methodname: 'reply_message',
            messageid: message.id,
            all,
        };

        const responses = await callServicesAndRefresh([request]);

        if (responses != null) {
            await navigate({
                tray: 'drafts',
                messageid: responses.pop() as number,
                courseid:
                    ['shortname', 'fullname'].includes(state.settings.filterbycourse) &&
                    oldParams.courseid != null
                        ? message.course.id
                        : undefined,
            });
        }
    };

    const savePreferences = async (preferences: Partial<Preferences>) => {
        patch({ preferences: { ...state.preferences, ...preferences } });
        let newParams = state.params;
        if (preferences.perpage) {
            newParams = {
                ...newParams,
                offset: state.params.offset
                    ? Math.trunc(state.params.offset / preferences.perpage) * preferences.perpage
                    : undefined,
            };
        }
        const request: SetPreferencesRequest = {
            methodname: 'set_preferences',
            preferences,
        };
        await callServicesAndRefresh([request], newParams, true);
    };

    const selectAll = (type: SelectAllType) => {
        patch({
            selectedMessages: new Map(
                state.listMessages
                    .filter(
                        (message) =>
                            type == 'all' ||
                            (type == 'read' && !message.unread) ||
                            (type == 'unread' && message.unread) ||
                            (type == 'starred' && message.starred) ||
                            (type == 'unstarred' && !message.starred),
                    )
                    .map((message) => [message.id, message]),
            ),
        });
    };

    const selectCourse = async (id?: number) => {
        await navigate({
            ...state.params,
            courseid: id,
            offset: 0,
            messageid: undefined,
            search: state.params.search
                ? { ...state.params.search, startid: undefined, reverse: undefined }
                : undefined,
        });
    };

    const sendMessage = async () => {
        if (!state.message?.draft) {
            return;
        }

        const request: ServiceRequest = {
            methodname: 'send_message',
            messageid: state.message.id,
        };

        const newParams: ViewParams = state.prevParams
            ? {
                  ...state.prevParams,
                  messageid: undefined,
                  offset:
                      Math.floor((state.prevParams.offset || 0) / state.preferences.perpage) *
                          state.preferences.perpage || undefined,
              }
            : ['shortname', 'fullname'].includes(state.settings.filterbycourse)
              ? { tray: 'inbox', courseid: state.message.course.id }
              : { tray: 'course', courseid: state.message.course.id };

        const responses = await callServicesAndRefresh([request], newParams);

        if (responses != null) {
            showToast({ text: state.strings.messagesent });
        }
    };

    const setDeleted = async (
        ids: ReadonlyArray<number>,
        deleted: DeletedStatus,
        allowUndo: boolean,
    ) => {
        const requests = ids.map(
            (id): SetDeletedRequest => ({
                methodname: 'set_deleted',
                messageid: id,
                deleted,
            }),
        );

        // Redirect if deleting message in single view.
        const params: ViewParams = { ...state.params, messageid: undefined };
        const responses = await callServicesAndRefresh(requests, params, true);

        if (responses != null && deleted != DeletedStatus.DeletedForever) {
            const string = deleted
                ? ids.length > 1
                    ? 'messagesmovedtotrash'
                    : 'messagemovedtotrash'
                : ids.length > 1
                  ? 'messagesrestored'
                  : 'messagerestored';
            const text = replaceStringParams(state.strings[string], ids.length);
            const undo = () => {
                setDeleted(ids, deleted ? DeletedStatus.NotDeleted : DeletedStatus.Deleted, false);
            };
            showToast({ text, undo: allowUndo ? undo : undefined });
        }
    };

    const setError = async (error?: ServiceError) => {
        patch({ loading: state.loading && !error, error });
    };

    const setLabels = async (added: number[], removed: number[]) => {
        updateMessages((message) => {
            if (!state.selectedMessages.has(message.id)) {
                return message;
            }
            return {
                ...message,
                labels: state.labels.filter((label) => {
                    if (added.includes(label.id)) {
                        return true;
                    } else if (removed.includes(label.id)) {
                        return false;
                    } else {
                        return message.labels.findIndex((l) => l.id == label.id) >= 0;
                    }
                }),
            };
        });

        const requests: SetLabelsRequest[] = [];
        state.selectedMessages.forEach((message) => {
            requests.push({
                methodname: 'set_labels',
                messageid: message.id,
                labelids: message.labels.map((label) => label.id),
            });
        });

        await callServicesAndRefresh(requests);
    };

    const setStarred = async (messageids: ReadonlyArray<number>, starred: boolean) => {
        updateMessages((message) =>
            messageids.includes(message.id) ? { ...message, starred } : message,
        );

        const requests = messageids.map(
            (messageid): SetStarredRequest => ({
                methodname: 'set_starred',
                messageid,
                starred,
            }),
        );

        await callServicesAndRefresh(requests);
    };

    const setUnread = async (messageids: ReadonlyArray<number>, unread: boolean) => {
        updateMessages((message) =>
            messageids.includes(message.id) ? { ...message, unread } : message,
        );

        const requests = messageids.map(
            (messageid): SetUnreadRequest => ({
                methodname: 'set_unread',
                messageid,
                unread,
            }),
        );

        const params: ViewParams = { ...state.params, messageid: undefined };

        await callServicesAndRefresh(requests, params);
    };

    const setViewportSize = (width: number) => {
        patch({
            viewportSize: width,
            navigationId: state.navigationId + 1, // Prevent list animations.
        });

        // Redirect to inbox on large screens if no tray is specified.
        if (!state.params.tray && width >= ViewportSize.LG && !state.error) {
            navigate({ tray: 'inbox', dialog: state.params.dialog }, true);
        }
    };

    const showDialog = async (dialog: Dialog) => {
        const params: ViewParams = { ...state.params, dialog };
        setUrlFromViewParams(params, state.params.dialog != null);
        patch({ params });
    };

    const showToast = async (toast: Toast) => {
        patch({ toasts: [toast] });
        if (toast) {
            window.setTimeout(() => hideToast(toast), 10000);
        }
    };

    const toggleSelected = (id: number) => {
        patch({
            selectedMessages: new Map(
                state.listMessages
                    .filter((message) =>
                        message.id == id
                            ? !state.selectedMessages.has(message.id)
                            : state.selectedMessages.has(message.id),
                    )
                    .map((message) => [message.id, message]),
            ),
        });
    };

    const undo = async (toast: Toast) => {
        if (toast.undo) {
            hideToast(toast);
            toast.undo();
        }
    };

    const updateDraft = (data: MessageData, noDelay: boolean) => {
        if (!state.message) {
            return;
        }

        const message = state.message;
        const handler = async () => {
            window.clearTimeout(draftTimeoutId);
            draftTimeoutId = 0;

            const data = state.draftData;
            if (!data) {
                return;
            }

            const requests: ServiceRequest[] = [
                {
                    methodname: 'update_message',
                    messageid: message.id,
                    data,
                },
                {
                    methodname: 'get_message',
                    messageid: message.id,
                },
            ];

            const responses = await callServicesAndSetError(requests);

            if (responses != null && data === state.draftData) {
                const updatedMessage = responses.pop() as Message;
                patch({
                    message: updatedMessage,
                    draftData: undefined,
                    draftSaved: true,
                });
            }
        };

        patch({ draftData: data, draftSaved: false });
        if (noDelay) {
            handler();
        } else if (!draftTimeoutId && state.settings.autosaveinterval > 0) {
            draftTimeoutId = window.setTimeout(handler, state.settings.autosaveinterval * 1000);
        }
    };

    const updateLabel = async (labelid: number, name: string, color: string) => {
        const request: UpdateLabelRequest = {
            methodname: 'update_label',
            labelid,
            name,
            color,
        };

        await callServicesAndRefresh([request]);
    };

    const updateMessages = (callback: <T extends MessageSummary>(message: T) => T) => {
        patch({
            listMessages: state.listMessages.map((message) => callback(message)),
            message: state.message ? callback(state.message) : undefined,
            selectedMessages: new Map(
                Array.from(state.selectedMessages.entries()).map(([id, message]) => [
                    id,
                    callback(message),
                ]),
            ),
        });
    };

    await navigate(getViewParamsFromUrl(), true, true);

    return {
        createLabel,
        createMessage,
        deleteLabel,
        emptyTrash,
        forward,
        get: (): State => state,
        hideDialog,
        hideToast,
        navigate,
        navigateToList,
        navigateToMenu,
        reply,
        savePreferences,
        selectAll,
        selectCourse,
        sendMessage,
        setDeleted,
        setError,
        setLabels,
        setStarred,
        setUnread,
        setViewportSize,
        showDialog,
        subscribe,
        toggleSelected,
        undo,
        updateDraft,
        updateLabel,
    };
}
