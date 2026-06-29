<!--
SPDX-FileCopyrightText: 2023-2024 Proyecto UNIMOODLE <direccion.area.estrategia.digital@uva.es>

SPDX-License-Identifier: GPL-3.0-or-later
-->
<svelte:options immutable={true} />

<script lang="ts">
    import type { Strings } from '../lib/state';

    export let strings: Strings;
    export let onClick: () => void;
</script>

<button
    type="button"
    class="btn btn-secondary text-truncate"
    title={strings.preferences}
    on:click={onClick}
>
    <i class="fa fa-cog" aria-hidden={true} />
</button>
