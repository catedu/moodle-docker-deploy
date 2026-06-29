<!--
SPDX-FileCopyrightText: 2023-2024 Proyecto UNIMOODLE <direccion.area.estrategia.digital@uva.es>

SPDX-License-Identifier: GPL-3.0-or-later
-->
<svelte:options immutable={true} />

<script lang="ts">
    import type { Store } from '../lib/store';

    export let store: Store;
</script>

<button
    type="button"
    class="local-mail-action-send btn btn-primary flex-shrink-0 text-nowrap px-3 text-truncate ml-auto"
    title={$store.strings.send}
    on:click={() => store.sendMessage()}
>
    <i class="fa fa-send mr-2" />
    {$store.strings.send}
</button>
