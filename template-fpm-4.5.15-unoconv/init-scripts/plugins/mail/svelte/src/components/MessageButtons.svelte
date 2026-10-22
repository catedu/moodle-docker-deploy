<!--
SPDX-FileCopyrightText: 2023-2024 Proyecto UNIMOODLE <direccion.area.estrategia.digital@uva.es>

SPDX-License-Identifier: GPL-3.0-or-later
-->
<svelte:options immutable={true} />

<script lang="ts">
    import type { Message } from '../lib/state';
    import type { Store } from '../lib/store';

    export let store: Store;
    export let message: Message;
    export let canReplyAll: boolean;
</script>

<button
    type="button"
    class="btn btn-primary mr-3 mb-3"
    on:click={() => store.reply(message, false)}
>
    <i class="fa fa-fw fa-reply mr-2" />
    {$store.strings.reply}
</button>
<button
    type="button"
    disabled={!canReplyAll}
    class:disabled={!canReplyAll}
    class="btn mr-3 mb-3"
    class:btn-primary={canReplyAll}
    class:btn-secondary={!canReplyAll}
    on:click={() => store.reply(message, true)}
>
    <i class="fa fa-fw fa-reply-all mr-2" />
    {$store.strings.replyall}
</button>
<button type="button" class="btn btn-primary mr-3 mb-3" on:click={() => store.forward(message)}>
    <i class="fa fa-fw fa-share mr-2" />
    {$store.strings.forward}
</button>
