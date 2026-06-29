<!--
SPDX-FileCopyrightText: 2023-2024 Proyecto UNIMOODLE <direccion.area.estrategia.digital@uva.es>
SPDX-FileCopyrightText: 2024-2025 Albert Gasset <albertgasset@fsfe.org>

SPDX-License-Identifier: GPL-3.0-or-later
-->
<svelte:options immutable={true} />

<script lang="ts">
    import type { Course, Settings, Strings } from '../lib/state';
    import { formatCourseName, formatNumber } from '../lib/utils';
    import ComboBox from './ComboBox.svelte';

    export let settings: Settings;
    export let strings: Strings;
    export let courses: ReadonlyArray<Course>;
    export let label: string;
    export let selected: number | undefined;
    export let required = false;
    export let readonly = false;
    export let buttonClass = '';
    export let dropdownAlign: 'left' | 'right' = 'left';
    export let onChange: (id?: number) => void;

    let comboBox: ComboBox;
    let currentCourse: Course | undefined;
    let entering = false;
    let inputText = '';

    $: currentCourse = courses.find((course) => course.id == selected);
    $: currentCourseName = formatCourseName(currentCourse, settings.filterbycourse);
    $: inputPattern = new RegExp(escape(inputText.trim()).replaceAll(/\s+/gu, '\\s+'), 'giu');
    $: dropdownCourses = courses.filter((course) =>
        formatCourseName(course, settings.filterbycourse).match(inputPattern),
    );
    $: courseHtml = (course: Course): string =>
        formatCourseName(course, settings.filterbycourse).replaceAll(inputPattern, (match) =>
            match.trim() ? '<mark>' + match + '</mark>' : match,
        );

    const escape = (text: string): string => text.replace(/[.*+?^${}()|[\]\\]/gu, '\\$&');

    const openDropdown = () => {
        inputText = '';
        entering = true;
        comboBox.focus();
    };

    const closeDropdown = () => {
        entering = false;
        inputText = '';
    };

    const selectAllCourses = async () => {
        await onChange();
        selected = undefined;
        closeDropdown();
    };

    const selectCourse = async (course: Course) => {
        await onChange(course.id);
        selected = course.id;
        closeDropdown();
    };
</script>

<ComboBox
    bind:this={comboBox}
    mode={readonly ? 'readonly' : entering ? 'input' : 'button'}
    bind:inputText
    inputPlaceholder={strings.course}
    leftIconClass="fa-graduation-cap"
    rightIconClass={readonly
        ? ''
        : !entering
          ? 'fa-caret-down'
          : inputText
            ? 'fa-times'
            : 'fa-caret-up'}
    rightIconLabel={entering && inputText ? strings.clearsearch : strings.changecourse}
    buttonText={currentCourseName || label}
    {buttonClass}
    readonlyText={currentCourseName || label}
    readonlyClass="alert-secondary"
    onBlur={closeDropdown}
    onButtonClick={openDropdown}
    onRightIconClick={entering ? closeDropdown : openDropdown}
>
    <div class="dropdown-menu dropdown-menu-{dropdownAlign}" class:show={entering}>
        {#if !required}
            <button
                type="button"
                class="dropdown-item text-truncate"
                on:click={() => selectAllCourses()}
            >
                {strings.allcourses}
            </button>

            <div class="dropdown-divider" />
        {/if}
        {#each dropdownCourses as course (course.id)}
            <button
                type="button"
                class="local-mail-select-course-item dropdown-item d-flex align-items-center"
                on:click={() => selectCourse(course)}
            >
                <span class="text-truncate">
                    <!-- eslint-disable-next-line svelte/no-at-html-tags -->
                    {@html courseHtml(course)}
                </span>
                {#if course.unread > 0}
                    <span class="local-mail-select-course-badge">{formatNumber(course.unread)}</span
                    >
                {/if}
            </button>
        {:else}
            <div class="px-4 text-danger">
                {strings.nocoursematchestext}
            </div>
        {/each}
    </div>
</ComboBox>

<style>
    .local-mail-select-course-badge {
        padding-left: 1rem;
        margin-left: auto;
        font-weight: bold;
        font-size: 0.75em;
    }

    .local-mail-select-course-item :global(mark) {
        padding-left: 0;
        padding-right: 0;
        background-color: rgba(255, 255, 0, 0.2);
        color: inherit;
    }

    .local-mail-select-course-item:not(:focus):hover {
        color: inherit;
        background-color: #eee;
    }
</style>
