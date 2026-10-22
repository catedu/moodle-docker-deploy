<!--
SPDX-FileCopyrightText: 2023-2024 Proyecto UNIMOODLE <direccion.area.estrategia.digital@uva.es>

SPDX-License-Identifier: GPL-3.0-or-later
-->
<svelte:options immutable={true} />

<script lang="ts">
    import type { Store } from '../lib/store';
    import ModalDialog from './ModalDialog.svelte';

    export let store: Store;
</script>

{#if $store.error}
    <ModalDialog title={$store.strings.error} onCancel={() => store.setError()}>
        <p>{$store.error?.message}</p>
        {#if $store.error?.debuginfo}
            <p>{$store.error?.debuginfo}</p>
        {/if}
        {#if $store.error?.backtrace || $store.error?.stacktrace}
            <pre>{$store.error?.backtrace || $store.error?.stacktrace}</pre>
        {/if}
    </ModalDialog>
{/if}
