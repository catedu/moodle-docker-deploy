<!--
SPDX-FileCopyrightText: 2023-2024 Proyecto UNIMOODLE <direccion.area.estrategia.digital@uva.es>

SPDX-License-Identifier: GPL-3.0-or-later
-->
<svelte:options immutable={true} />

<script lang="ts">
    import type { Strings } from '../lib/state';

    export let strings: Strings;
    export let iconOnly = false;
    export let onClick: () => void;
</script>

<button
    type="button"
    class="btn btn-primary text-truncate px-2 px-sm-3"
    class:flex-shrink-0={iconOnly}
    aria-label={strings.compose}
    on:click={onClick}
>
    <i class="fa fa-edit mr-0 mr-sm-1" />
    <span class:d-none={iconOnly}>
        {strings.compose}
    </span>
</button>
