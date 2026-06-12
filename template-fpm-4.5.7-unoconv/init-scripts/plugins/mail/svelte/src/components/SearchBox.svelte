<!--
SPDX-FileCopyrightText: 2023-2024 Proyecto UNIMOODLE <direccion.area.estrategia.digital@uva.es>
SPDX-FileCopyrightText: 2024-2025 Albert Gasset <albertgasset@fsfe.org>

SPDX-License-Identifier: GPL-3.0-or-later
-->
<svelte:options immutable={true} />

<script lang="ts">
    import { tick } from 'svelte';
    import type { SearchParams, ViewParams } from '../lib/state';
    import type { Store } from '../lib/store';
    import ComboBox from './ComboBox.svelte';
    import SearchOptions from './SearchOptions.svelte';
    import IncrementalSearch from './IncrementalSearch.svelte';

    export let store: Store;

    let entering = !$store.params.search;
    let advancedExpanded = false;
    let comboBox: ComboBox;
    let searchOptions: SearchOptions;
    let content = '';
    let sendername = '';
    let recipientname = '';
    let unread = false;
    let withfilesonly = false;
    let maxtime = 0;
    let loading = false;

    const updateFields = (search?: SearchParams) => {
        content = search?.content || '';
        sendername = search?.sendername || '';
        recipientname = search?.recipientname || '';
        unread = search?.unread || false;
        withfilesonly = search?.withfilesonly || false;
        maxtime = search?.maxtime || 0;
    };

    $: search = $store.params.search;

    $: updateFields(search);

    $: advancedEnabled = Boolean(
        search?.sendername ||
            search?.recipientname ||
            search?.unread ||
            search?.withfilesonly ||
            search?.maxtime,
    );

    $: searchEnabled = Boolean(search?.content) || advancedEnabled;

    $: submitEnabled = Boolean(
        content.trim() ||
            sendername.trim() ||
            recipientname.trim() ||
            unread ||
            withfilesonly ||
            maxtime,
    );

    $: searchFields = [
        { label: '', value: content },
        { label: $store.strings.searchfrom, value: sendername },
        { label: $store.strings.searchto, value: recipientname },
        {
            label: $store.strings.searchdate,
            value: maxtime > 0 ? new Date(maxtime * 1000).toLocaleDateString() : '',
        },
        { label: $store.strings.searchunreadonly, value: unread },
        { label: $store.strings.searchhasattachments, value: withfilesonly },
    ].filter(({ value }) => Boolean(value));

    const startEntering = async () => {
        entering = true;
        if (advancedEnabled) {
            advancedExpanded = true;
        }
        await tick();
        comboBox.focus();
    };

    const stopEntering = async () => {
        entering = !searchEnabled;
        advancedExpanded = false;
        updateFields(search);
    };

    const toggleDropdown = async () => {
        if (advancedExpanded) {
            advancedExpanded = false;
            await tick();
            comboBox.focus();
        } else {
            entering = true;
            advancedExpanded = true;
            await tick();
            searchOptions.focus();
        }
    };

    const cancel = async () => {
        entering = true;
        advancedExpanded = false;
        updateFields();
        await store.navigate({
            ...$store.params,
            offset: undefined,
            search: undefined,
        });
        await tick();
        comboBox.focus();
    };

    const submit = async () => {
        await store.navigate({
            ...$store.params,
            messageid: undefined,
            offset: undefined,
            search: advancedExpanded
                ? {
                      content: content.trim(),
                      sendername: sendername.trim(),
                      recipientname: recipientname.trim(),
                      unread,
                      withfilesonly,
                      maxtime,
                  }
                : {
                      content: content.trim(),
                  },
        });
        advancedExpanded = false;
        entering = false;
    };

    const handleIncrementalSearchClick = async (params: ViewParams) => {
        await store.navigate(params);
        advancedExpanded = false;
        entering = false;
    };
</script>

<ComboBox
    bind:this={comboBox}
    mode={entering ? 'input' : 'button'}
    bind:inputText={content}
    inputPlaceholder={$store.strings.search}
    leftIconClass={loading ? 'fa-spinner fa-pulse' : 'fa-search'}
    rightIconClass={advancedExpanded ? 'fa-caret-up' : 'fa-sliders'}
    rightIconLabel={$store.strings.searchoptions}
    middleIconClass={searchEnabled || submitEnabled ? 'fa-times' : undefined}
    middleIconLabel={$store.strings.clearsearch}
    buttonClass="alert alert-primary"
    onBlur={stopEntering}
    onEnter={submitEnabled ? submit : cancel}
    onRightIconClick={toggleDropdown}
    onMiddleIconClick={cancel}
    onButtonClick={startEntering}
>
    <span slot="buttonContent">
        {#each searchFields as { label, value }, i}
            {#if i > 0}<span class="dimmed_text">,&ensp;</span>{/if}
            {#if value === true}
                <span class="dimmed_text">{label}</span>
            {:else}
                {#if label}<span class="dimmed_text">{label}: </span>{/if}
                {value}
            {/if}
        {/each}
    </span>

    {#if advancedExpanded}
        <SearchOptions
            bind:this={searchOptions}
            {store}
            bind:sendername
            bind:recipientname
            bind:unread
            bind:withfilesonly
            bind:maxtime
            onSubmit={submitEnabled ? submit : cancel}
            onCancel={cancel}
        />
    {/if}
    {#if $store.settings.incrementalsearch}
        <IncrementalSearch
            {store}
            enabled={entering && !advancedExpanded && !!content.trim()}
            {content}
            bind:loading
            onClick={handleIncrementalSearchClick}
        />
    {/if}
</ComboBox>
