<!--
SPDX-FileCopyrightText: 2023-2024 Proyecto UNIMOODLE <direccion.area.estrategia.digital@uva.es>

SPDX-License-Identifier: GPL-3.0-or-later
-->
<svelte:options immutable={true} />

<script lang="ts">
    import { fade } from 'svelte/transition';
    import type { Store } from '../lib/store';
    import { viewUrl } from '../lib/url';

    export let store: Store;

    $: recentParams = {
        tray: $store.params.tray,
        courseid: $store.params.courseid,
        labelid: $store.params.labelid,
    };
</script>

{#if !$store.listMessages.length}
    <div in:fade={{ delay: 400 }} class="alert alert-info">
        <div>
            {$store.strings.nomessagesfound}
        </div>
        {#if $store.totalCount > 0}
            <a
                class="btn btn-info text-white mt-3"
                href={viewUrl(recentParams)}
                on:click|preventDefault={() => store.navigate(recentParams)}
            >
                {$store.strings.showrecentmessages}
            </a>
        {/if}
    </div>
{/if}
