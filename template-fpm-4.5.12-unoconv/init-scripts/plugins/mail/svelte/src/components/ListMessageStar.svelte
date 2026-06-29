<!--
SPDX-FileCopyrightText: 2023-2024 Proyecto UNIMOODLE <direccion.area.estrategia.digital@uva.es>
SPDX-FileCopyrightText: 2025 Albert Gasset <albertgasset@fsfe.org>

SPDX-License-Identifier: GPL-3.0-or-later
-->
<svelte:options immutable={true} />

<script lang="ts">
    import type { MessageSummary } from '../lib/state';
    import type { Store } from '../lib/store';

    export let store: Store;
    export let message: MessageSummary;

    $: starClass = message.starred ? 'fa-star text-warning' : 'fa-star-o';
</script>

<button
    type="button"
    class="btn px-2 border-0"
    role="checkbox"
    aria-checked={message.starred}
    disabled={message.deleted}
    title={message.deleted
        ? $store.strings[message.starred ? 'starred' : 'unstarred']
        : $store.strings[message.starred ? 'markasunstarred' : 'markasstarred']}
    on:click|preventDefault|stopPropagation={() => store.setStarred([message.id], !message.starred)}
>
    <i class="fa {starClass}" />
</button>
