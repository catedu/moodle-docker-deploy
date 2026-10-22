<!--
SPDX-FileCopyrightText: 2023-2024 Proyecto UNIMOODLE <direccion.area.estrategia.digital@uva.es>
SPDX-FileCopyrightText: 2024-2025 Albert Gasset <albertgasset@fsfe.org>

SPDX-License-Identifier: GPL-3.0-or-later
-->
<svelte:options immutable={true} />

<script lang="ts">
    import type { Store } from '../lib/store';
    import { dateFromTimestamp, timestampFromDate } from '../lib/utils';
    import HelpPopover from './HelpPopover.svelte';

    export let store: Store;
    export let sendername = '';
    export let recipientname = '';
    export let unread = false;
    export let withfilesonly = false;
    export let maxtime = 0;
    export let onSubmit: () => void;
    export let onCancel: () => void;

    export function focus() {
        senderNode.focus();
    }

    let senderNode: HTMLElement;
    let today = dateFromTimestamp(Math.floor(new Date().getTime() / 1000));

    $: maxdate = dateFromTimestamp(maxtime);

    const updateMaxTime = (event: Event) => {
        maxtime = timestampFromDate((event.target as HTMLInputElement).value);
    };

    const handleInputKey = (event: KeyboardEvent) => {
        if (event.key == 'Enter') {
            onSubmit();
        } else if (event.key == 'Escape') {
            onCancel();
        }
    };
</script>

<div class="dropdown-menu show p-3 w-100" style="min-width: 18rem">
    <div class="form-group row">
        <label for="local-mail-search-input-sendername" class="col-4 col-form-label pr-0">
            {$store.strings.searchfrom}
        </label>
        <div class="col-8">
            <input
                type="text"
                class="form-control"
                id="local-mail-search-input-sendername"
                bind:value={sendername}
                bind:this={senderNode}
                on:keyup={handleInputKey}
            />
        </div>
    </div>
    <div class="form-group row">
        <label for="local-mail-search-input-recipientname" class="col-4 col-form-label pr-0">
            {$store.strings.searchto}
        </label>
        <div class="col-8 p">
            <input
                type="text"
                class="form-control"
                id="local-mail-search-input-recipientname"
                bind:value={recipientname}
                on:keyup={handleInputKey}
            />
        </div>
    </div>
    <div class="form-group row">
        <label for="local-mail-search-input-maxdate" class="col-4 col-form-label pr-0">
            {$store.strings.searchdate}
            <HelpPopover {store} message={$store.strings.searchdatehelp} />
        </label>
        <div class="col-8">
            <input
                type="date"
                class="form-control"
                id="local-mail-search-input-maxdate"
                max={today}
                value={maxdate}
                on:input={updateMaxTime}
                on:keyup={handleInputKey}
            />
        </div>
    </div>

    <div class="d-flex flex-wrap align-items-center" style="column-gap: 2rem; row-gap: 1rem">
        <div class="form-check">
            <input
                class="form-check-input"
                type="checkbox"
                id="local-mail-search-input-unread"
                bind:checked={unread}
                on:keyup={handleInputKey}
            />
            <label class="form-check-label" for="local-mail-search-input-unread">
                {$store.strings.searchunreadonly}
            </label>
        </div>
        <div class="form-check">
            <input
                class="form-check-input"
                type="checkbox"
                id="local-mail-search-input-withfilesonly"
                bind:checked={withfilesonly}
                on:keyup={handleInputKey}
            />
            <label class="form-check-label" for="local-mail-search-input-withfilesonly">
                {$store.strings.searchhasattachments}
            </label>
        </div>
        <button
            type="button"
            class="btn btn-primary px-3 ml-auto"
            on:click={onSubmit}
            on:keyup={handleInputKey}
        >
            {$store.strings.search}
        </button>
    </div>
</div>
