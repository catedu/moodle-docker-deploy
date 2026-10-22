<!--
SPDX-FileCopyrightText: 2023-2024 Proyecto UNIMOODLE <direccion.area.estrategia.digital@uva.es>
SPDX-FileCopyrightText: 2025 Albert Gasset <albertgasset@fsfe.org>

SPDX-License-Identifier: GPL-3.0-or-later
-->
<svelte:options immutable={true} />

<script lang="ts">
    import { truncate } from '../actions/truncate';
    import type { Course, Settings } from '../lib/state';
    import { formatCourseName } from '../lib/utils';

    export let course: Course;
    export let settings: Settings;

    $: text = formatCourseName(course, settings.coursebadges);
    $: length = settings.coursebadgeslength || 20;
</script>

{#if ['shortname', 'fullname'].includes(settings.coursebadges)}
    <span
        class="local-mail-course-badge badge px-2 mr-2 mb-2"
        use:truncate={text}
        style="min-width: 3rem; max-width: calc({length}ch + 1.5rem)"
    >
        {text}
    </span>
{/if}

<style>
    .local-mail-course-badge {
        font-size: inherit;
        font-weight: inherit;
        color: var(--local-mail-color-gray-fg);
        background-color: var(--local-mail-color-gray-bg);
        padding-top: 0;
        padding-bottom: 0;
        line-height: inherit;
    }
</style>
