/*
 * SPDX-FileCopyrightText: 2023-2024 Proyecto UNIMOODLE <direccion.area.estrategia.digital@uva.es>
 * SPDX-FileCopyrightText: 2024 Albert Gasset <albertgasset@fsfe.org>
 *
 * SPDX-License-Identifier: GPL-3.0-or-later
 */

import { loadModule, type CoreAjax } from './amd';
import {
    DeletedStatus,
    type Course,
    type Group,
    type Label,
    type Message,
    type MessageData,
    type MessageForm,
    type MessageSummary,
    type Preferences,
    type Role,
    type User,
} from './state';

export type ServiceRequest =
    | SetPreferencesRequest
    | GetCoursesRequest
    | GetLabelsRequest
    | CountMessagesRequest
    | SearchMessagesRequest
    | GetMessageRequest
    | ViewMessageRequest
    | SetUnreadRequest
    | SetStarredRequest
    | SetDeletedRequest
    | EmptyTrashRequest
    | CreateLabelRequest
    | UpdateLabelRequest
    | DeleteLabelRequest
    | SetLabelsRequest
    | GetRolesRequest
    | GetGroupsRequest
    | SearchUsersRequest
    | GetMessageFormRequest
    | CreateMessageRequest
    | ReplyMessageRequest
    | ForwardMessageRequest
    | UpdateMessageRequest
    | SendMessageRequest;

export interface SetPreferencesRequest {
    readonly methodname: 'set_preferences';
    readonly preferences: Partial<Preferences>;
}

export type SetPreferencesResponse = void;

export interface GetCoursesRequest {
    readonly methodname: 'get_courses';
}

export type GetCoursesResponse = ReadonlyArray<Course>;

export interface GetLabelsRequest {
    readonly methodname: 'get_labels';
}

export type GetLabelsResponse = ReadonlyArray<Label>;

export interface MessageQuery {
    readonly courseid?: number;
    readonly labelid?: number;
    readonly draft?: boolean;
    readonly roles?: ReadonlyArray<string>;
    readonly unread?: boolean;
    readonly starred?: boolean;
    readonly deleted?: boolean;
    readonly content?: string;
    readonly sendername?: string;
    readonly recipientname?: string;
    readonly withfilesonly?: boolean;
    readonly maxtime?: number;
    readonly startid?: number;
    readonly stopid?: number;
    readonly reverse?: boolean;
}

export interface CountMessagesRequest {
    readonly methodname: 'count_messages';
    readonly query: MessageQuery;
}

export type CountMessagesResponse = number;

export interface SearchMessagesRequest {
    readonly methodname: 'search_messages';
    readonly query: MessageQuery;
    readonly offset?: number;
    readonly limit?: number;
}

export type SearchMessagesResponse = ReadonlyArray<MessageSummary>;

export interface GetMessageRequest {
    readonly methodname: 'get_message';
    readonly messageid: number;
}

export type GetMessageResponse = Message;

export interface ViewMessageRequest {
    readonly methodname: 'view_message';
    readonly messageid: number;
}

export type ViewMessageResponse = null;

export interface SetUnreadRequest {
    readonly methodname: 'set_unread';
    readonly messageid: number;
    readonly unread: boolean;
}

export type SetUnreadResponse = void;

export interface SetStarredRequest {
    readonly methodname: 'set_starred';
    readonly messageid: number;
    readonly starred: boolean;
}

export type SetStarredResponse = void;

export interface SetDeletedRequest {
    readonly methodname: 'set_deleted';
    readonly messageid: number;
    readonly deleted: DeletedStatus;
}

export type SetDeletedResponse = void;

export interface EmptyTrashRequest {
    readonly methodname: 'empty_trash';
    readonly courseid?: number;
}

export type EmptyTrashResponse = void;

export interface CreateLabelRequest {
    readonly methodname: 'create_label';
    readonly name: string;
    readonly color: string;
    readonly messageids: ReadonlyArray<number>;
}

export type CreateLabelResponse = void;

export interface UpdateLabelRequest {
    readonly methodname: 'update_label';
    readonly labelid: number;
    readonly name: string;
    readonly color: string;
}

export type UpdateLabelResponse = void;

export interface DeleteLabelRequest {
    readonly methodname: 'delete_label';
    readonly labelid: number;
}

export type DeleteLabelResponse = void;

export interface SetLabelsRequest {
    readonly methodname: 'set_labels';
    readonly messageid: number;
    readonly labelids: ReadonlyArray<number>;
}

export type SetLabelsResponse = void;

export interface GetRolesRequest {
    readonly methodname: 'get_roles';
    readonly courseid: number;
}

export type GetRolesResponse = ReadonlyArray<Role>;

export interface GetGroupsRequest {
    readonly methodname: 'get_groups';
    readonly courseid: number;
}

export type GetGroupsResponse = ReadonlyArray<Group>;

export interface UserQuery {
    readonly courseid: number;
    readonly roleid?: number;
    readonly groupid?: number;
    readonly fullname?: string;
    readonly include?: number[];
}

export interface SearchUsersRequest {
    readonly methodname: 'search_users';
    readonly query: UserQuery;
    readonly offset?: number;
    readonly limit?: number;
}

export type SearchUsersResponse = ReadonlyArray<User>;

export interface GetMessageFormRequest {
    readonly methodname: 'get_message_form';
    readonly messageid: number;
}

export type GetMessageFormeResponse = MessageForm;

export interface CreateMessageRequest {
    readonly methodname: 'create_message';
    readonly courseid: number;
}

export type CreateMessageResponse = number;

export interface ReplyMessageRequest {
    readonly methodname: 'reply_message';
    readonly messageid: number;
    readonly all: boolean;
}

export type ReplyMessageResponse = number;

export interface ForwardMessageRequest {
    readonly methodname: 'forward_message';
    readonly messageid: number;
}

export type ForwardMessageResponse = number;

export interface UpdateMessageRequest {
    readonly methodname: 'update_message';
    readonly messageid: number;
    readonly data: MessageData;
}

export type UpdateMessageResponse = void;

export interface SendMessageRequest {
    readonly methodname: 'send_message';
    readonly messageid: number;
}

export type SendMessageResponse = void;

interface ServiceCall {
    promise: Promise<unknown[] | null>;
    cancel: () => void;
}

let currentCall: ServiceCall | undefined;

const cancellableMethods = new Set([
    'get_courses',
    'get_labels',
    'count_messages',
    'search_messages',
    'get_message',
    'view_message',
    'get_roles',
    'get_groups',
    'search_users',
    'get_message_form',
]);

/**
 * Calls one or more web service methods in a single HTTP request.
 *
 * @param requests List of request with method name and arguments.
 * @returns A promise to the array of responses or null if cancelled.
 */
export function callServices(requests: ServiceRequest[]): Promise<unknown[] | null> {
    let cancelled = false;

    const start = async () => {
        const ajax = await loadModule<CoreAjax>('core/ajax');
        const ajaxRequests = requests
            .filter((request) => !cancelled || !cancellableMethods.has(request.methodname))
            .map(({ methodname, ...args }) => ({
                methodname: `local_mail_${methodname}`,
                args,
            }));

        let responses: unknown[] = [];

        if (ajaxRequests.length > 0) {
            try {
                responses = await Promise.all(ajax.call(ajaxRequests));
            } catch (error) {
                throw typeof error == 'string' ? { message: error } : error;
            }
        }

        return cancelled ? null : responses;
    };

    currentCall?.cancel();

    currentCall = {
        // Chain promise to avoid concurrent web service calls.
        promise: currentCall ? currentCall.promise.then(start, start) : start(),
        cancel: () => {
            cancelled = true;
        },
    };

    return currentCall.promise;
}
