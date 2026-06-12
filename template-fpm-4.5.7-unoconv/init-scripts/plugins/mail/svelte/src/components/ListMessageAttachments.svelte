<!--
SPDX-FileCopyrightText: 2023-2024 Proyecto UNIMOODLE <direccion.area.estrategia.digital@uva.es>

SPDX-License-Identifier: GPL-3.0-or-later
-->
<svelte:options immutable={true} />

<script lang="ts">
    import { ViewportSize, type MessageSummary } from '../lib/state';
    import type { Store } from '../lib/store';

    export let store: Store;
    export let message: MessageSummary;
</script>

{#if message.numattachments > 0 || $store.viewportSize >= ViewportSize.MD}
    <span
        class="-shrink-0 mr-2"
        aria-hidden={message.numattachments == 0}
        title={message.numattachments > 0 ? $store.strings.hasattachments : undefined}
    >
        <i class="fa fa-fw {message.numattachments > 0 ? 'fa-paperclip' : ''}" aria-hidden={true} />
    </span>
{/if}
