<!--
SPDX-FileCopyrightText: 2023-2024 Proyecto UNIMOODLE <direccion.area.estrategia.digital@uva.es>
SPDX-FileCopyrightText: 2025 Albert Gasset <albertgasset@fsfe.org>

SPDX-License-Identifier: GPL-3.0-or-later
-->
<svelte:options immutable={true} />

<script lang="ts">
    import type { Label } from '../lib/state';
    import { colors } from '../lib/utils';

    export let label: Label;

    $: color = colors.includes(label.color) ? label.color : 'gray';
</script>

<span
    class="local-mail-label-badge badge flex-shrink-0 px-2 mr-2 mb-2"
    style={`color: var(--local-mail-color-${color}-fg, var(--local-mail-color-gray-fg));` +
        `background-color: var(--local-mail-color-${color}-bg, var(--local-mail-color-gray-bg))`}
>
    {label.name}
</span>

<style>
    .local-mail-label-badge {
        font-size: inherit;
        font-weight: inherit;
        color: var(--local-mail-color-gray-fg);
        background-color: var(--local-mail-color-gray-bg);
        padding-top: 0;
        padding-bottom: 0;
        line-height: inherit;
    }
</style>
