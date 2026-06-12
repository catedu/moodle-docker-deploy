<!--
SPDX-FileCopyrightText: 2023-2024 Proyecto UNIMOODLE <direccion.area.estrategia.digital@uva.es>

SPDX-License-Identifier: GPL-3.0-or-later
-->
<svelte:options immutable={true} />

<script lang="ts">
    import CourseBadge from './CourseBadge.svelte';
    import LabelBadge from './LabelBadge.svelte';
    import { ViewportSize, type MessageSummary } from '../lib/state';
    import type { Store } from '../lib/store';

    export let store: Store;
    export let message: MessageSummary;
</script>

{#if $store.viewportSize < ViewportSize.MD}
    {#if $store.params.courseid != message.course.id}
        <CourseBadge course={message.course} settings={$store.settings} />
    {/if}
{/if}
{#each message.labels as label (label.id)}
    {#if $store.params.tray != 'label' || $store.params.labelid != label.id}
        <LabelBadge {label} />
    {/if}
{/each}
{#if $store.viewportSize >= ViewportSize.MD}
    {#if $store.params.courseid != message.course.id}
        <CourseBadge course={message.course} settings={$store.settings} />
    {/if}
{/if}
