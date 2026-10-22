<!--
SPDX-FileCopyrightText: 2023-2024 Proyecto UNIMOODLE <direccion.area.estrategia.digital@uva.es>
SPDX-FileCopyrightText: 2025 Albert Gasset <albertgasset@fsfe.org>

SPDX-License-Identifier: GPL-3.0-or-later
-->
<svelte:options immutable={true} />

<script lang="ts">
    import type { Reference, Strings } from '../lib/state';
    import MessageAttachments from './MessageAttachments.svelte';
    import MessageContent from './MessageContent.svelte';
    import UserFullName from './UserFullName.svelte';
    import UserPicture from './UserPicture.svelte';

    export let strings: Strings;
    export let reference: Reference;
</script>

<div class="card mb-4">
    <div class="card-body p-3 px-xl-4">
        <h5 class="h5 card-title mb-3">
            {reference.subject}
        </h5>
        <div class="local-mail-message-reference-user-and-date d-sm-flex">
            <div class="d-flex mb-3 mb-sm-0">
                <div class="mr-3">
                    <UserPicture user={reference.sender} />
                </div>
                <div class="mt-1">
                    <UserFullName user={reference.sender} />
                </div>
            </div>
            <div class="mt-1 ml-auto">
                {reference.fulltime}
            </div>
        </div>
        <hr />
        <MessageContent content={reference.content} />
        {#if reference.attachments.length > 0}
            <hr />
            <MessageAttachments {strings} message={reference} />
        {/if}
    </div>
</div>

<style>
    .local-mail-message-reference-user-and-date {
        margin-bottom: -0.25rem;
    }
</style>
