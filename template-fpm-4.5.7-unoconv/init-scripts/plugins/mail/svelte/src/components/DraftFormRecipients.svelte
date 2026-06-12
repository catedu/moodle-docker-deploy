<!--
SPDX-FileCopyrightText: 2023-2024 Proyecto UNIMOODLE <direccion.area.estrategia.digital@uva.es>

SPDX-License-Identifier: GPL-3.0-or-later
-->
<svelte:options immutable={true} />

<script lang="ts">
    import { fade } from 'svelte/transition';

    import { truncate } from '../actions/truncate';
    import { RecipientType, type Recipient, type User } from '../lib/state';
    import type { Store } from '../lib/store';
    import UserPicture from './UserPicture.svelte';

    export let store: Store;
    export let recipients: ReadonlyMap<number, Recipient>;
    export let onDelete: (user: User) => unknown;
</script>

{#each Object.values(RecipientType) as type}
    {@const users = Array.from(recipients.values()).filter((user) => user.type == type)}
    {#if users.length}
        <div transition:fade class=" d-flex mb-2">
            <div class="local-mail-draft-form-recipients-type flex-shrink-0 py-2 mr-2">
                {$store.strings[type]}:
            </div>
            <div class="d-flex flex-wrap" style="min-width: 0">
                {#each users as user (user.id)}
                    <div
                        transition:fade
                        use:truncate={user.fullname}
                        class="local-mail-draft-form-recipients-user alert d-flex flex-shrink align-items-center border-0 p-0 mr-2 mb-2"
                        class:alert-danger={!user.isvalid}
                    >
                        <div class="d-flex m-1 mr-2">
                            {#if user.isvalid}
                                <UserPicture {user} />
                            {:else}
                                <div
                                    class="m-0 d-flex justify-content-center align-items-center"
                                    style="width: 35px; height: 35px"
                                    title={$store.strings.cannotsendmailtouser}
                                >
                                    <i class="fa fa-exclamation-circle" />
                                </div>
                            {/if}
                        </div>
                        <div class="py-2" use:truncate={user.fullname}>
                            {user.fullname}
                        </div>
                        <button
                            type="button"
                            class="btn align-self-middle p-2 align-bottom"
                            title={$store.strings.delete}
                            on:click={() => onDelete(user)}
                        >
                            <i class="fa fa-fw fa-times" />
                        </button>
                    </div>
                {/each}
            </div>
        </div>
    {/if}
{/each}

<style>
    .local-mail-draft-form-recipients-type {
        width: 3rem;
    }
    .local-mail-draft-form-recipients-user {
        border-radius: 0.5rem;
        max-width: 20rem;
    }
    .local-mail-draft-form-recipients-user:not(.alert-danger) {
        background-color: #eee;
    }
    .local-mail-draft-form-recipients-user .btn {
        position: relative;
        z-index: 100;
    }
</style>
