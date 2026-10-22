<!--
SPDX-FileCopyrightText: 2023-2024 Proyecto UNIMOODLE <direccion.area.estrategia.digital@uva.es>
SPDX-FileCopyrightText: 2024-2025 Albert Gasset <albertgasset@fsfe.org>

SPDX-License-Identifier: GPL-3.0-or-later
-->
<svelte:options immutable={true} />

<script lang="ts">
    import type { Store } from '../lib/store';
    import DeleteButton from './DeleteButton.svelte';
    import DeleteForeverButton from './DeleteForeverButton.svelte';
    import LabelsButton from './LabelsButton.svelte';
    import MoreActionsButton from './MoreActionsButton.svelte';
    import PagingButtons from './PagingButtons.svelte';
    import RestoreButton from './RestoreButton.svelte';

    export let store: Store;
</script>

<div role="toolbar" class="local-mail-toolbar px-2 py-3 bg-light border-top border-bottom">
    <div class="d-flex justify-content-around" role="group">
        {#if $store.params.tray == 'trash'}
            <RestoreButton {store} bottom={true} />
            <DeleteForeverButton {store} bottom={true} />
        {:else}
            <LabelsButton {store} bottom={true} />
            <DeleteButton {store} bottom={true} />
        {/if}
        <MoreActionsButton {store} bottom={true} />
        <PagingButtons {store} bottom={true} />
    </div>
</div>

<style>
    .local-mail-toolbar {
        z-index: 1020;
        position: sticky;
        bottom: 0;
    }
</style>
