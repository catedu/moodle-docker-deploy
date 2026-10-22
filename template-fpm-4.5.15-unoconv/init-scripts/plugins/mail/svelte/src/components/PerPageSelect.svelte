<!--
SPDX-FileCopyrightText: 2023-2024 Proyecto UNIMOODLE <direccion.area.estrategia.digital@uva.es>
SPDX-FileCopyrightText: 2025 Albert Gasset <albertgasset@fsfe.org>

SPDX-License-Identifier: GPL-3.0-or-later
-->
<svelte:options immutable={true} />

<script lang="ts">
    import type { Store } from '../lib/store';
    export let store: Store;

    const defaultValues: ReadonlyArray<number> = [5, 10, 20, 50, 100];

    $: values = defaultValues.includes($store.preferences.perpage)
        ? defaultValues
        : defaultValues.concat([$store.preferences.perpage]).sort((a, b) => a - b);

    let selected: number = $store.preferences.perpage;
</script>

<div class="form-inline justify-content-end mt-3">
    <div class="form-group">
        <label for="local-mail-perpage-select">{$store.strings.messagesperpage}:</label>
        <select
            id="local-mail-perpage-select"
            class="local-mail-perpage-select-select custom-select"
            bind:value={selected}
            on:change={() => store.savePreferences({ perpage: selected })}
        >
            {#each values as value}
                <option {value}>{value}</option>
            {/each}
        </select>
    </div>
</div>

<style>
    .local-mail-perpage-select-select {
        width: auto;
        margin-left: 0.5rem;
    }
</style>
