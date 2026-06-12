<!--
SPDX-FileCopyrightText: 2023-2024 Proyecto UNIMOODLE <direccion.area.estrategia.digital@uva.es>

SPDX-License-Identifier: GPL-3.0-or-later
-->
<svelte:options immutable={true} />

<script lang="ts">
    import { afterUpdate } from 'svelte';
    import { loadModule, type CoreFiltersEvents } from '../lib/amd';

    export let content: string;

    let node: Element;

    afterUpdate(async () => {
        const events = await loadModule<CoreFiltersEvents>('core_filters/events');
        events.notifyFilterContentUpdated([node]);
    });
</script>

<div class="local-mail-message-content" bind:this={node}>
    <!-- eslint-disable-next-line svelte/no-at-html-tags -->
    {@html content}
</div>

<style>
    .local-mail-message-content {
        max-width: 60rem;
    }
</style>
