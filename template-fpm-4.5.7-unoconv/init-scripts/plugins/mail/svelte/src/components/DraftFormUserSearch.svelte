<!--
SPDX-FileCopyrightText: 2023-2024 Proyecto UNIMOODLE <direccion.area.estrategia.digital@uva.es>
SPDX-FileCopyrightText: 2024-2025 Albert Gasset <albertgasset@fsfe.org>

SPDX-License-Identifier: GPL-3.0-or-later
-->
<svelte:options immutable={true} />

<script lang="ts">
    import { truncate } from '../actions/truncate';
    import { callServices, type ServiceRequest } from '../lib/services';
    import {
        RecipientType,
        type Recipient,
        type ServiceError,
        type User,
        GroupMode,
    } from '../lib/state';
    import type { Store } from '../lib/store';
    import ComboBox from './ComboBox.svelte';
    import UserPicture from './UserPicture.svelte';

    export let store: Store;
    export let recipients: ReadonlyMap<number, Recipient>;
    export let onChange: (users: ReadonlyArray<User>, type: RecipientType | null) => unknown;

    const DELAY = 500;

    let expanded = false;
    let text = '';
    let comboBox: ComboBox;
    let loading = false;
    let timeoutId: number | undefined;
    let users: ReadonlyArray<User> = [];
    let error = '';
    let roleid = 0;
    let groupid = 0;

    $: course = $store.message?.course;
    $: roleid = $store.draftRoles?.find((role) => role.id == roleid) ? roleid : 0;
    $: groupid = $store.draftGroups?.find((group) => group.id == groupid)
        ? groupid
        : $store.draftGroups?.[0]?.id || 0;

    const handleToggleClick = async () => {
        if (expanded) {
            text = '';
            expanded = false;
            window.clearTimeout(timeoutId);
        } else {
            search(false);
            comboBox.focus();
        }
    };

    const search = async (throttle: boolean) => {
        const limit = $store.settings.usersearchlimit;

        loading = true;
        window.clearTimeout(timeoutId);

        if (course?.groupmode != GroupMode.No && !$store.draftGroups?.length) {
            loading = false;
            expanded = true;
            error = $store.strings.errornogroups;
            return;
        }

        timeoutId = window.setTimeout(
            async () => {
                const request: ServiceRequest = {
                    methodname: 'search_users',
                    query: {
                        courseid: course?.id || 0,
                        fullname: text,
                        roleid,
                        groupid,
                    },
                    limit: limit + 1,
                };
                let responses: unknown[] | null;
                try {
                    responses = await callServices([request]);
                } catch (error) {
                    store.setError(error as ServiceError);
                    return;
                }
                if (responses == null) {
                    return;
                }
                users = responses.pop() as ReadonlyArray<User>;
                if (!users.length) {
                    error = $store.strings.nousersfound;
                } else if (users.length > limit) {
                    error = $store.strings.toomanyusersfound;
                } else {
                    error = '';
                }
                users = users.slice(0, limit);
                loading = false;
                expanded = true;
            },
            throttle ? DELAY : 0,
        );
    };

    const handleBlur = () => {
        text = '';
        expanded = false;
    };

    const handleFocus = () => {
        if (!expanded) {
            search(false);
        }
    };
</script>

<div class="local-mail-draft-form-user-search form-group">
    <ComboBox
        bind:this={comboBox}
        mode="input"
        bind:inputText={text}
        inputPlaceholder={$store.strings.addrecipients}
        leftIconClass={loading ? 'fa-spinner fa-pulse' : 'fa-user'}
        rightIconClass={text ? 'fa-times' : expanded ? 'fa-caret-up' : 'fa-caret-down'}
        rightIconLabel={$store.strings.addrecipients}
        onRightIconClick={handleToggleClick}
        invalid={!recipients.size}
        onFocus={handleFocus}
        onBlur={handleBlur}
        onInput={() => search(true)}
    >
        <div
            class="local-mail-draft-form-user-search-dropdown dropdown-menu p-0 w-100"
            class:show={expanded}
            style="min-width: 18rem"
        >
            <div class="list-group-item d-sm-flex px-2 py-2">
                <div class="flex-grow-1 mx-2">
                    <select
                        class="form-control custom-select w-100 text-truncate bg-transparent"
                        bind:value={roleid}
                        on:change={() => search(false)}
                    >
                        <option value={0}>{$store.strings.allroles}</option>
                        {#each $store.draftRoles ?? [] as role (role.id)}
                            <option value={role.id}>
                                {role.name}
                            </option>
                        {/each}
                    </select>
                </div>
                {#if ($store.draftGroups ?? []).length > 0}
                    <div class="flex-grow-1 mx-2 mt-2 mt-sm-0">
                        <select
                            class="form-control text-truncate"
                            style="min-width: 0"
                            disabled={($store.draftGroups ?? []).length == 1}
                            bind:value={groupid}
                            on:change={() => search(false)}
                        >
                            {#each $store.draftGroups ?? [] as group (group.id)}
                                <option value={group.id} class="text-truncate">
                                    {group.name}
                                </option>
                            {/each}
                        </select>
                    </div>
                {/if}
            </div>
            {#if error}
                <div class="list-group-item text-danger">
                    {error}
                </div>
            {:else}
                <div class="list-group-item d-flex align-items-sm-center p-0">
                    <div class="mx-3 my-2">
                        <UserPicture icon="fa-users" />
                    </div>
                    <div class="d-sm-flex align-items-center flex-grow-1">
                        <div class="py-2 mr-3" use:truncate={$store.strings.allusers}>
                            {$store.strings.allusers}
                        </div>
                        <div class="d-flex ml-auto mr-2">
                            {#each Object.values(RecipientType) as type}
                                {@const all = users.every(
                                    (user) => recipients.get(user.id)?.type == type,
                                )}
                                <button
                                    type="button"
                                    class="btn text-nowrap mr-2 mb-2 mt-sm-2"
                                    class:btn-primary={all}
                                    class:btn-secondary={!all}
                                    aria-pressed={all}
                                    on:click={() => onChange(users, all ? null : type)}
                                >
                                    {$store.strings[type]}
                                </button>
                            {/each}
                        </div>
                    </div>
                </div>
                {#each users as user (user.id)}
                    {@const recipientType = recipients.get(user.id)?.type}
                    <div class="list-group-item d-flex p-0">
                        <div class="mx-3 my-2">
                            <UserPicture {user} />
                        </div>
                        <div class="d-sm-flex flex-grow-1">
                            <div class="py-2 mr-3 align-self-center">
                                {user.fullname}
                            </div>
                            <div class="d-flex ml-auto mr-2 align-self-start">
                                {#each Object.values(RecipientType) as type}
                                    <button
                                        type="button"
                                        class="btn text-nowrap mr-2 mb-2 mt-sm-2"
                                        class:btn-primary={recipientType == type}
                                        class:btn-secondary={recipientType != type}
                                        aria-pressed={recipientType == type}
                                        on:click={() =>
                                            onChange([user], recipientType == type ? null : type)}
                                    >
                                        {$store.strings[type]}
                                    </button>
                                {/each}
                            </div>
                        </div>
                    </div>
                {/each}
            {/if}
        </div>
    </ComboBox>
</div>

<style>
    .local-mail-draft-form-user-search :global(.list-group-item) {
        background: none;
    }

    .local-mail-draft-form-user-search-dropdown {
        max-height: 50vh;
        max-width: 50rem;
        overflow-y: scroll;
    }
</style>
