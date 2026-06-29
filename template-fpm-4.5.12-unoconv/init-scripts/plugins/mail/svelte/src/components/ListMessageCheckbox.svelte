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

    $: checkClass = $store.selectedMessages.has(message.id) ? 'fa-check-square-o' : 'fa-square-o';
</script>

<button
    type="button"
    class="btn px-2 d-flex align-items-center border-0"
    role="checkbox"
    aria-checked={Boolean($store.selectedMessages.has(message.id))}
    title={$store.strings.select}
    on:click|preventDefault|stopPropagation={() => store.toggleSelected(message.id)}
>
    <i class="fa {checkClass}" />
</button>
