<!--
SPDX-FileCopyrightText: 2023-2024 Proyecto UNIMOODLE <direccion.area.estrategia.digital@uva.es>

SPDX-License-Identifier: GPL-3.0-or-later
-->
<svelte:options immutable={true} />

<script lang="ts">
    import { blur } from '../actions/blur';
    import type { SelectAllType } from '../lib/state';
    import type { Store } from '../lib/store';

    export let store: Store;

    let expanded = false;

    const closeMenu = () => {
        expanded = false;
    };

    const toggleMenu = () => {
        expanded = !expanded;
    };

    const selectAll = (type: SelectAllType) => {
        closeMenu();
        store.selectAll(type);
    };

    $: iconClass =
        $store.selectedMessages.size == 0
            ? 'fa-square-o'
            : $store.selectedMessages.size < $store.listMessages.length
              ? 'fa-minus-square-o'
              : 'fa-check-square-o';
</script>

<div class="btn-group" role="group" use:blur={closeMenu}>
    <button
        class="btn btn-secondary dropdown-toggle"
        aria-expanded={expanded}
        title={$store.strings.select}
        on:click={toggleMenu}
    >
        <i class="fa fa-fw {iconClass}" />
    </button>
    {#if expanded}
        <div class="dropdown-menu show">
            <button type="button" class="dropdown-item" on:click={() => selectAll('all')}>
                {$store.strings.all}
            </button>
            <button type="button" class="dropdown-item" on:click={() => selectAll('none')}>
                {$store.strings.none}
            </button>
            <button type="button" class="dropdown-item" on:click={() => selectAll('read')}>
                {$store.strings.read}
            </button>
            <button type="button" class="dropdown-item" on:click={() => selectAll('unread')}>
                {$store.strings.unread}
            </button>
            <button type="button" class="dropdown-item" on:click={() => selectAll('starred')}>
                {$store.strings.starred}
            </button>
            <button type="button" class="dropdown-item" on:click={() => selectAll('unstarred')}>
                {$store.strings.unstarred}
            </button>
        </div>
    {/if}
</div>
