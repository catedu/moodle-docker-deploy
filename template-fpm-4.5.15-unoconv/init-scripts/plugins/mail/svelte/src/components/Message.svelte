<!--
SPDX-FileCopyrightText: 2023-2024 Proyecto UNIMOODLE <direccion.area.estrategia.digital@uva.es>
SPDX-FileCopyrightText: 2024-2025 Albert Gasset <albertgasset@fsfe.org>

SPDX-License-Identifier: GPL-3.0-or-later
-->
<svelte:options immutable={true} />

<script lang="ts">
    import type { Message } from '../lib/state';
    import type { Store } from '../lib/store';
    import HtmlHead from './HtmlHead.svelte';
    import NessageActions from './MessageActions.svelte';
    import MessageAttachments from './MessageAttachments.svelte';
    import MessageButtons from './MessageButtons.svelte';
    import MessageContent from './MessageContent.svelte';
    import MessageLabels from './MessageLabels.svelte';
    import MessageReference from './MessageReference.svelte';
    import MessageUsers from './MessageUsers.svelte';

    export let store: Store;
    export let message: Message;

    $: canReplyAll =
        message.sender.id == $store.userid
            ? message.recipients.filter((u) => u.type == 'cc').length > 0
            : message.recipients.filter(
                  (u) => ['to', 'cc'].includes(u.type) && u.id != $store.userid,
              ).length > 0;
</script>

<HtmlHead javascript={message.javascript} />

<div class="card">
    <div class="card-body p-3 px-xl-4">
        <h3 class="h4 card-title mb-3 mb-md-2">
            {message.subject}
        </h3>
        <div class="d-md-flex align-items-start mb-2">
            <div class="local-mail-message-labels d-flex flex-wrap" style="min-width: 0">
                <MessageLabels {store} {message} />
            </div>
            <div class="d-flex justify-content-between flex-shrink-0 ml-auto">
                <div class="my-2 text-truncate">
                    {message.fulltime}
                </div>
                <div class="local-mail-message-actions d-flex flex-shrink-0">
                    <NessageActions {store} {message} {canReplyAll} />
                </div>
            </div>
        </div>
        <MessageUsers {store} {message} />
        <hr />
        <MessageContent content={message.content} />
        {#if message.attachments.length > 0}
            <hr />
            <MessageAttachments strings={$store.strings} {message} />
        {/if}
        <hr />
        <div class="local-mail-message-buttons d-flex flex-column flex-sm-row justify-content-end">
            <MessageButtons {store} {message} {canReplyAll} />
        </div>
    </div>
</div>

{#if message.references.length > 0}
    <div class="alert alert-secondary mt-4 mb-4 text-center">
        {$store.strings.references}
    </div>
    {#each message.references as reference (reference.id)}
        <MessageReference strings={$store.strings} {reference} />
    {/each}
{/if}

<style>
    .local-mail-message-actions {
        margin-left: 1rem;
        margin-right: -0.5rem;
    }

    .local-mail-message-buttons {
        margin-top: 1rem;
        margin-right: -1rem;
        margin-bottom: -1rem;
    }

    .local-mail-message-labels {
        margin-top: 0.5rem;
        margin-left: -0.5rem;
        margin-right: -0.5rem;
    }
</style>
