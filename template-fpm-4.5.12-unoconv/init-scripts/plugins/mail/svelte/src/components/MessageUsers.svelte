<!--
SPDX-FileCopyrightText: 2023-2024 Proyecto UNIMOODLE <direccion.area.estrategia.digital@uva.es>
SPDX-FileCopyrightText: 2025 Albert Gasset <albertgasset@fsfe.org>

SPDX-License-Identifier: GPL-3.0-or-later
-->
<svelte:options immutable={true} />

<script lang="ts">
    import { RecipientType, type Message } from '../lib/state';
    import type { Store } from '../lib/store';
    import UserFullName from './UserFullName.svelte';
    import UserPicture from './UserPicture.svelte';

    export let store: Store;
    export let message: Message;

    $: recipients = (type: string) => {
        return message.recipients.filter((user) => user.type == type);
    };
</script>

<div class="local-mail-message-users d-flex">
    <div class="mr-3">
        <UserPicture user={message.sender} />
    </div>
    <div class="d-flex flex-column">
        <div class="mt-1 mb-2">
            <UserFullName user={message.sender} />
        </div>
        {#each Object.values(RecipientType) as type}
            {#if recipients(type).length > 0}
                <div class="mb-2">
                    <span> {$store.strings[type]}: </span>
                    {#each recipients(type) as user, i (user.id)}
                        {#if i > 0},
                        {/if}
                        <UserFullName {user} />
                    {/each}
                </div>
            {/if}
        {/each}
    </div>
</div>

<style>
    .local-mail-message-users {
        margin-bottom: -0.5rem;
    }
</style>
