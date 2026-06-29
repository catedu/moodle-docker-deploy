<!--
SPDX-FileCopyrightText: 2023-2024 Proyecto UNIMOODLE <direccion.area.estrategia.digital@uva.es>
SPDX-FileCopyrightText: 2024-2025 Albert Gasset <albertgasset@fsfe.org>

SPDX-License-Identifier: GPL-3.0-or-later
-->
<svelte:options immutable={true} />

<script lang="ts">
    import { blur } from '../actions/blur';
    import { busy } from '../actions/busy';
    import type { Course, Label, Settings, Strings, ViewParams } from '../lib/state';
    import { viewUrl } from '../lib/url';
    import { formatNumber } from '../lib/utils';
    import ComposeButton from './ComposeButton.svelte';
    import MenuComponent from './Menu.svelte';
    import PreferencesButton from './PreferencesButton.svelte';

    export let settings: Settings;
    export let strings: Strings;
    export let courses: ReadonlyArray<Course>;
    export let labels: ReadonlyArray<Label>;
    export let params: ViewParams;
    export let loading: boolean;
    export let onClick: (params: ViewParams) => void;
    export let onComposeClick: (courseid?: number) => void;
    export let onCourseChange: (courseid?: number) => void;

    let expanded = false;

    $: unread = courses.reduce((acc, course) => acc + course.unread, 0);

    const closeMenu = () => {
        expanded = false;
    };

    const handleComposeClick = () => {
        closeMenu();
        onComposeClick(params.courseid);
    };

    const handleIconClick = (event: Event) => {
        event.preventDefault();
        if (settings.globaltrays.length > 0 || labels.length > 0) {
            expanded = !expanded;
        } else {
            onClick({ tray: 'inbox' });
        }
    };

    const handleMenuClick = (params: ViewParams) => {
        closeMenu();
        onClick(params);
    };

    const handlePreferencesClick = () => {
        closeMenu();
        onClick({ ...params, dialog: 'preferences' });
    };
</script>

<div
    class="local-mail local-mail-navbar pop-over-region h-100"
    class:popover-region-toggle={expanded}
    use:blur={closeMenu}
    use:busy={loading}
>
    <a
        aria-expanded={expanded}
        class="nav-link btn h-100 position-relative d-flex align-items-center px-2 py-0 rounded-0"
        href={viewUrl({ tray: 'inbox' })}
        title={strings.pluginname}
        on:click={handleIconClick}
    >
        <i class="fa fa-fw fa-envelope-o icon m-0" aria-label={strings.plugginname} />
        {#if loading}
            <div class="local-mail-navbar-spinner position-absolute">
                <i class="fa fa-fw fa-spinner fa-pulse text-primary" />
            </div>
        {:else if unread > 0}
            <div class="local-mail-navbar-count count-container">{formatNumber(unread)}</div>
        {/if}
    </a>
    {#if expanded}
        <div class="local-mail-navbar-popover popover-region-container">
            <div class="d-flex justify-content-between p-2">
                <ComposeButton {strings} onClick={handleComposeClick} />
                <PreferencesButton {strings} onClick={handlePreferencesClick} />
            </div>
            <MenuComponent
                {settings}
                {strings}
                {courses}
                {labels}
                {params}
                navbar={true}
                onClick={handleMenuClick}
                {onCourseChange}
            />
        </div>
    {/if}
</div>

<style>
    .local-mail-navbar-count {
        top: 50% !important;
        transform: translateY(-16px);
    }

    .local-mail-navbar.popover-region-toggle::after {
        border-bottom-color: var(--light, var(--bs-gray-100));
    }

    .local-mail-navbar-popover {
        width: 20rem;
        height: auto;
        bottom: unset;
        overflow-y: auto;
        background-color: var(--light, var(--bs-gray-100));
    }

    .local-mail-navbar-popover :global(.list-group-item:not(.list-group-item-primary)) {
        background-color: transparent;
    }

    .local-mail-navbar-popover :global(.list-group-item:not(.list-group-item-primary):hover) {
        background-color: rgba(0, 0, 0, 0.025);
    }

    .local-mail-navbar-spinner {
        top: 50% !important;
        right: 0;
        transform: translateY(-18px);
    }

    .local-mail-navbar-spinner .fa {
        font-size: 16px;
    }
</style>
