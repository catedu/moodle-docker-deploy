<!--
SPDX-FileCopyrightText: 2023-2024 Proyecto UNIMOODLE <direccion.area.estrategia.digital@uva.es>

SPDX-License-Identifier: GPL-3.0-or-later
-->
<svelte:options immutable={true} />

<script lang="ts">
    import { ViewportSize, type MessageSummary } from '../lib/state';
    import type { Store } from '../lib/store';

    export let message: MessageSummary;
    export let store: Store;
</script>

<span
    class="local-mail-list-message-time d-shrink-0 text-nowrap text-right"
    class:local-mail-list-message-time-md={$store.viewportSize >= ViewportSize.MD}
    title={message.fulltime}
>
    {message.shorttime}
</span>

<style>
    .local-mail-list-message-time-md {
        min-width: 5rem;
    }
</style>
