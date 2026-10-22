<!--
SPDX-FileCopyrightText: 2023-2024 Proyecto UNIMOODLE <direccion.area.estrategia.digital@uva.es>

SPDX-License-Identifier: GPL-3.0-or-later
-->
<svelte:options immutable={true} />

<script lang="ts">
    import type { Store } from '../lib/store';

    export let store: Store;
    export let message: string;
</script>

<button
    type="button"
    class="btn btn-help p-0"
    data-container="body"
    data-toggle="popover"
    data-placement="top"
    data-content={message}
    data-trigger="focus"
    aria-label={$store.strings.help}
>
    <i class="fa fa-fw fa-question-circle text-info" role="img" title={$store.strings.help} />
</button>
