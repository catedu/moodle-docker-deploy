<!--
SPDX-FileCopyrightText: 2023-2024 Proyecto UNIMOODLE <direccion.area.estrategia.digital@uva.es>

SPDX-License-Identifier: GPL-3.0-or-later
-->
<svelte:options immutable={true} />

<script lang="ts">
    import type { Message } from '../lib/state';
    import type { Store } from '../lib/store';
    import CourseBadge from './CourseBadge.svelte';
    import LabelBadge from './LabelBadge.svelte';

    export let store: Store;
    export let message: Message;
</script>

{#if message.course.id != $store.params.courseid}
    <CourseBadge course={message.course} settings={$store.settings} />
{/if}
{#each message.labels as label (label.id)}
    <LabelBadge {label} />
{/each}
