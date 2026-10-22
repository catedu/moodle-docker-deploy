<!--
SPDX-FileCopyrightText: 2023-2024 Proyecto UNIMOODLE <direccion.area.estrategia.digital@uva.es>

SPDX-License-Identifier: GPL-3.0-or-later
-->
<svelte:options immutable={true} />

<script lang="ts">
    import type { Course, Strings } from '../lib/state';
    import { createUrl } from '../lib/url';

    export let userid: number;
    export let strings: Strings;
    export let courses: ReadonlyArray<Course>;
    export let id: number;
    export let courseid: number;

    $: course = courses.find((course) => course.id == courseid);
</script>

{#if id != userid && course}
    <a class="btn" role="button" href={createUrl(courseid, [id], 'to')}>
        <i class="fa fa-envelope-o mr-1" aria-hidden="true" />
        {strings.sendmail}
    </a>
{/if}
