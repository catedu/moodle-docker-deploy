<!--
SPDX-FileCopyrightText: 2023-2024 Proyecto UNIMOODLE <direccion.area.estrategia.digital@uva.es>

SPDX-License-Identifier: GPL-3.0-or-later
-->
<svelte:options immutable={true} />

<script lang="ts">
    import { blur } from '../actions/blur';
    import { ViewportSize, type Message } from '../lib/state';
    import type { Store } from '../lib/store';

    export let store: Store;
    export let message: Message;
    export let canReplyAll: boolean;

    let expanded = false;

    $: starClass = message.starred ? 'fa-star text-warning' : 'fa-star-o';

    const toggleMenu = () => {
        expanded = !expanded;
    };

    const closeMenu = () => {
        expanded = false;
    };
</script>

<button
    type="button"
    class="btn py-2 border-0"
    role="checkbox"
    aria-checked={message.starred}
    disabled={message.deleted}
    title={message.starred ? $store.strings.markasunstarred : $store.strings.markasstarred}
    on:click={() => store.setStarred([message.id], !message.starred)}
>
    <i class="fa {starClass}" />
</button>

{#if $store.viewportSize < ViewportSize.SM}
    <div class="dropdown" use:blur={closeMenu}>
        <button
            type="button"
            class="btn"
            aria-expanded={expanded}
            title={$store.strings.more}
            on:click={toggleMenu}
        >
            <i class="fa fa-fw fa-ellipsis-v align-middle" />
        </button>
        {#if expanded}
            <div class="dropdown-menu dropdown-menu-right show">
                <button
                    type="button"
                    class="dropdown-item"
                    on:click={() => store.reply(message, false)}
                >
                    <i class="fa fa-fw fa-reply" aria-hidden="true" />
                    {$store.strings.reply}
                </button>
                <button
                    type="button"
                    disabled={!canReplyAll}
                    class="dropdown-item"
                    class:disabled={!canReplyAll}
                    on:click={() => store.reply(message, true)}
                >
                    <i class="fa fa-fw fa-reply-all" aria-hidden="true" />
                    {$store.strings.replyall}
                </button>
                <button type="button" class="dropdown-item" on:click={() => store.forward(message)}>
                    <i class="fa fa-fw fa-share" aria-hidden="true" />
                    {$store.strings.forward}
                </button>
            </div>
        {/if}
    </div>
{/if}

{#if $store.viewportSize >= ViewportSize.SM}
    <button
        type="button"
        title={$store.strings.reply}
        class="btn py-2 border-0"
        on:click={() => store.reply(message, false)}
    >
        <i class="fa fa-fw fa-reply" aria-hidden="true" />
    </button>
    <button
        type="button"
        disabled={!canReplyAll}
        class:disabled={!canReplyAll}
        title={$store.strings.replyall}
        class="btn py-2 border-0"
        on:click={() => store.reply(message, true)}
    >
        <i class="fa fa-fw fa-reply-all" aria-hidden="true" />
    </button>
    <button
        type="button"
        title={$store.strings.forward}
        class="btn py-2 border-0"
        on:click={() => store.forward(message)}
    >
        <i class="fa fa-fw fa-share" aria-hidden="true" />
    </button>
{/if}
