<!--
SPDX-FileCopyrightText: 2023-2024 Proyecto UNIMOODLE <direccion.area.estrategia.digital@uva.es>
SPDX-FileCopyrightText: 2024 Albert Gasset <albertgasset@fsfe.org>

SPDX-License-Identifier: GPL-3.0-or-later
-->
<svelte:options immutable={true} />

<script lang="ts">
    import {
        type Course,
        type Label,
        type Settings,
        type Strings,
        type Tray,
        type ViewParams,
    } from '../lib/state';
    import { formatCourseName } from '../lib/utils';
    import CourseSelect from './CourseSelect.svelte';
    import MenuItem from './MenuItem.svelte';

    export let settings: Settings;
    export let strings: Strings;
    export let courses: ReadonlyArray<Course>;
    export let labels: ReadonlyArray<Label>;
    export let params: ViewParams;
    export let navbar = false;
    export let onClick: ((params: ViewParams) => void) | undefined = undefined;
    export let onCourseChange: (courseid?: number) => void;

    $: unread = courses.reduce((acc, course) => acc + course.unread, 0);
    $: drafts = courses.reduce((acc, course) => acc + course.drafts, 0);

    $: trayVisible = (type: Tray): boolean => {
        return settings.globaltrays.includes(type) || params.tray == type;
    };

    $: courseVisible = (course: Course): boolean => {
        return (
            settings.coursetrays == 'all' ||
            (settings.coursetrays == 'unread' && (course.unread || 0) > 0) ||
            (params.tray == 'course' && params.courseid == course.id)
        );
    };

    $: filterenabled = ['shortname', 'fullname'].includes(settings.filterbycourse);
    $: courseid = filterenabled ? params.courseid : undefined;
    $: search = params.search
        ? {
              content: params.search.content,
              sendername: params.search.sendername,
              recipientname: params.search.recipientname,
              maxtime: params.search.maxtime,
              unread: params.search.unread,
              withfilesonly: params.search.withfilesonly,
          }
        : undefined;
</script>

<div
    class="list-group"
    class:local-mail-menu-navbar={navbar}
    class:list-group-flush={navbar}
    class:border-top={navbar}
>
    {#if filterenabled || courseid}
        <CourseSelect
            {settings}
            {strings}
            {courses}
            label={strings.allcourses}
            selected={params.courseid}
            buttonClass="btn-secondary"
            onChange={onCourseChange}
        />
    {/if}
    <MenuItem
        icon="fa-inbox"
        text={strings.inbox}
        count={courseid ? courses.find((c) => c.id == courseid)?.unread : unread}
        params={{ tray: 'inbox', courseid, search }}
        active={params.tray == 'inbox'}
        {onClick}
    />
    {#if trayVisible('starred')}
        <MenuItem
            icon="fa-star"
            text={strings.starredplural}
            params={{ tray: 'starred', courseid, search }}
            active={params.tray == 'starred'}
            {onClick}
        />
    {/if}
    {#if trayVisible('sent')}
        <MenuItem
            icon="fa-paper-plane"
            text={strings.sentplural}
            params={{ tray: 'sent', courseid, search }}
            active={params.tray == 'sent'}
            {onClick}
        />
    {/if}
    {#if trayVisible('drafts')}
        <MenuItem
            icon="fa-file"
            text={strings.drafts}
            count={courseid ? courses.find((c) => c.id == courseid)?.drafts : drafts}
            params={{ tray: 'drafts', courseid, search }}
            active={params.tray == 'drafts'}
            {onClick}
        />
    {/if}
    {#if trayVisible('trash')}
        <MenuItem
            icon="fa-trash"
            text={strings.trash}
            params={{ tray: 'trash', courseid, search }}
            active={params.tray == 'trash'}
            {onClick}
        />
    {/if}
    {#each labels as label (label.id)}
        <MenuItem
            icon="fa-tag"
            text={label.name}
            count={courseid ? label.courses.find((c) => c.id == courseid)?.unread : label.unread}
            color={label.color}
            params={{ tray: 'label', labelid: label.id, courseid, search }}
            active={params.tray == 'label' && params.labelid == label.id}
            {onClick}
        />
    {/each}
    {#each courses as course (course.id)}
        {#if courseVisible(course) && (!filterenabled || course.id != courseid)}
            <MenuItem
                icon="fa-graduation-cap"
                text={formatCourseName(course, settings.coursetraysname)}
                count={course.unread}
                params={filterenabled
                    ? { tray: 'inbox', courseid: course.id, search }
                    : { tray: 'course', courseid: course.id, search }}
                active={(params.tray == 'course' || (!params.tray && !filterenabled)) &&
                    params.courseid == course.id}
                {onClick}
            />
        {/if}
    {/each}
</div>

<style>
    .local-mail-menu-navbar :global(a:focus),
    .local-mail-menu-navbar :global(.btn:focus),
    .local-mail-menu-navbar :global(.form-control:focus) {
        box-shadow: inset 0 0 0 0.2rem var(--primary);
        outline: none;
    }
</style>
