<!--
SPDX-FileCopyrightText: 2023-2024 Proyecto UNIMOODLE <direccion.area.estrategia.digital@uva.es>
SPDX-FileCopyrightText: 2025 Albert Gasset <albertgasset@fsfe.org>

SPDX-License-Identifier: GPL-3.0-or-later
-->
<svelte:options immutable={true} />

<script lang="ts">
    import type { Message } from '../lib/state';
    import type { Store } from '../lib/store';
    import LabelBadge from './LabelBadge.svelte';

    export let store: Store;
    export let message: Message;
</script>

<div class="local-mail-draft-form-time-and-labels d-sm-flex justify-content-between mb-2">
    <div class="d-flex flex-grow-1">
        <div
            class="align-self-center alert alert-info m-0 px-2 py-0 text-truncate"
            title={message.fulltime}
        >
            {#if $store.draftSaved}
                <i class="fa fa-check mr-1" aria-hidden="true" /> {$store.strings.draftsaved}
            {:else}
                <i class="fa fa-clock-o mr-1" aria-hidden="true" /> {message.shorttime}
            {/if}
        </div>
        <button
            type="button"
            class="btn border-0 py-2 ml-auto mr-2 mr-sm-0"
            role="checkbox"
            aria-checked={message.starred}
            disabled={message.deleted}
            title={message.starred ? $store.strings.markasunstarred : $store.strings.markasstarred}
            on:click={() => store.setStarred([message.id], !message.starred)}
        >
            <i class="fa {message.starred ? 'fa-star text-warning' : 'fa-star-o'}" />
        </button>
    </div>

    {#if message.labels}
        <div class="d-flex flex-wrap mt-2 mr-2" style="min-width: 0">
            {#each message.labels as label (label.id)}
                <LabelBadge {label} />
            {/each}
        </div>
    {/if}
</div>

<style>
    .local-mail-draft-form-time-and-labels {
        margin-right: -15px;
    }
</style>
