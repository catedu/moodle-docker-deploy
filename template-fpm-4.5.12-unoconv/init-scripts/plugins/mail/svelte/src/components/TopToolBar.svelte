<!--
SPDX-FileCopyrightText: 2023-2024 Proyecto UNIMOODLE <direccion.area.estrategia.digital@uva.es>
SPDX-FileCopyrightText: 2024-2025 Albert Gasset <albertgasset@fsfe.org>

SPDX-License-Identifier: GPL-3.0-or-later
-->
<svelte:options immutable={true} />

<script lang="ts">
    import { ViewportSize } from '../lib/state';
    import type { Store } from '../lib/store';
    import BackButton from './BackButton.svelte';
    import CourseSelect from './CourseSelect.svelte';
    import DeleteButton from './DeleteButton.svelte';
    import DeleteForeverButton from './DeleteForeverButton.svelte';
    import LabelsButton from './LabelsButton.svelte';
    import MoreActionsButton from './MoreActionsButton.svelte';
    import PagingButtons from './PagingButtons.svelte';
    import RestoreButton from './RestoreButton.svelte';
    import SelectAllButton from './SelectAllButton.svelte';
    import SendButton from './SendButton.svelte';

    export let store: Store;
</script>

<div role="toolbar" class="local-mail-toolbar d-flex w-100">
    {#if $store.message}
        <BackButton {store} />
    {:else}
        <SelectAllButton {store} />
    {/if}

    {#if $store.viewportSize >= ViewportSize.MD}
        <div class="btn-group" role="group">
            {#if $store.params.tray == 'trash'}
                <RestoreButton {store} />
                <DeleteForeverButton {store} />
            {:else}
                <LabelsButton {store} />
                <DeleteButton {store} />
            {/if}
            <MoreActionsButton {store} />
        </div>
    {/if}
    {#if !$store.message?.draft && ['shortname', 'fullname'].includes($store.settings.filterbycourse) && $store.viewportSize < ViewportSize.LG}
        <div
            class="d-flex flex-grow-1 ml-auto mr-0 ml-md-0 mr-md-auto"
            style="max-width: 20rem; min-width: 0"
        >
            <CourseSelect
                settings={$store.settings}
                strings={$store.strings}
                courses={$store.courses}
                label={$store.strings.allcourses}
                selected={$store.params.courseid}
                readonly={$store.params.tray == 'course'}
                buttonClass="alert alert-primary"
                dropdownAlign={$store.viewportSize < ViewportSize.MD ? 'right' : 'left'}
                onChange={(id) => store.selectCourse(id)}
            />
        </div>
    {/if}

    {#if $store.viewportSize >= ViewportSize.MD}
        <PagingButtons {store} />
    {:else if $store.message?.draft}
        <SendButton {store} />
    {/if}
</div>

<style>
    .local-mail-toolbar {
        column-gap: 1rem;
    }
</style>
