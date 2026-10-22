<!--
SPDX-FileCopyrightText: 2023-2024 Proyecto UNIMOODLE <direccion.area.estrategia.digital@uva.es>
SPDX-FileCopyrightText: 2024-2025 Albert Gasset <albertgasset@fsfe.org>

SPDX-License-Identifier: GPL-3.0-or-later
-->
<svelte:options immutable={true} />

<script lang="ts">
    import { truncate } from '../actions/truncate';
    import type { ViewParams } from '../lib/state';
    import { viewUrl } from '../lib/url';
    import { formatNumber } from '../lib/utils';

    export let icon: string;
    export let text: string;
    export let params: ViewParams;
    export let count = 0;
    export let disabled = false;
    export let active = false;
    export let color: string | undefined = undefined;
    export let onClick: ((params: ViewParams) => void) | undefined = undefined;

    const handleClick = (event: Event) => {
        if (onClick) {
            event.preventDefault();
            onClick(params);
        }
    };
</script>

<a
    class="local-mail-menu-item list-group-item list-group-item-action d-flex align-items-center px-3 py-2"
    class:list-group-item-primary={active}
    class:disabled
    aria-current={active}
    aria-disabled={disabled}
    role="tab"
    href={viewUrl(params)}
    on:click={handleClick}
    style={color && !active ? `color: var(--local-mail-color-${color}-fg)` : ''}
>
    <i
        class="fa {icon} fa-fw"
        aria-hidden="true"
        style={color && !active ? `color: var(--local-mail-color-${color}-bg)` : ''}
    />
    <span class="flex-fill px-2" use:truncate={text}>{text}</span>
    {#if count > 0}
        <span class="badge text-dark">{formatNumber(count)}</span>
    {/if}
</a>

<style>
    .local-mail-menu-item:not(:first-child) {
        border-top-width: 0;
    }

    .local-mail-menu-item:focus {
        z-index: 3;
    }
</style>
