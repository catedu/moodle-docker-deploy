<!--
SPDX-FileCopyrightText: 2023-2024 Proyecto UNIMOODLE <direccion.area.estrategia.digital@uva.es>
SPDX-FileCopyrightText: 2024 Albert Gasset <albertgasset@fsfe.org>

SPDX-License-Identifier: GPL-3.0-or-later
-->
<svelte:options immutable={true} />

<script lang="ts">
    import { blur } from '../actions/blur';
    import type { Store } from '../lib/store';
    import LabelModal from './LabelModal.svelte';

    export let store: Store;
    export let bottom = false;

    let expanded = false;

    let selectedLabels: ReadonlyMap<number, 'false' | 'mixed' | 'true'> = new Map();

    $: selectedLabels = new Map(
        $store.labels.map((label) => {
            const messages = Array.from($store.selectedMessages.values()).filter((message) =>
                message.labels.some((messageLabel) => messageLabel.id == label.id),
            );
            return [
                label.id,
                messages.length == 0
                    ? 'false'
                    : messages.length < $store.selectedMessages.size
                      ? 'mixed'
                      : 'true',
            ];
        }),
    );

    $: applyEnabled = Array.from(selectedLabels.entries()).some(([labelid, selected]) =>
        Array.from($store.selectedMessages.values()).some(
            (message) =>
                (selected == 'true' && message.labels.every((label) => label.id != labelid)) ||
                (selected == 'false' && message.labels.some((label) => label.id == labelid)),
        ),
    );

    $: labelIconClass = (labelid: number) => {
        if (selectedLabels.get(labelid) == 'false') {
            return 'fa-square-o';
        } else if (selectedLabels.get(labelid) == 'mixed') {
            return 'fa-minus-square-o';
        } else {
            return 'fa-check-square-o';
        }
    };

    const closeMenu = () => {
        expanded = false;
    };

    const toggleMenu = () => {
        expanded = !expanded;
    };

    const toggleLabel = (labelid: number) => {
        selectedLabels = new Map(
            Array.from(selectedLabels.entries()).map(([id, selected]) => [
                id,
                id == labelid ? (selected == 'true' ? 'false' : 'true') : selected,
            ]),
        );
    };

    const applyLabels = () => {
        expanded = false;
        store.setLabels(
            Array.from(selectedLabels.keys()).filter((id) => selectedLabels.get(id) == 'true'),
            Array.from(selectedLabels.keys()).filter((id) => selectedLabels.get(id) == 'false'),
        );
    };

    const newLabel = () => {
        closeMenu();
        store.showDialog('createlabel');
    };

    const createLabel = async (name: string, color: string) => {
        store.hideDialog();
        store.createLabel(name, color);
    };
</script>

<div class="btn-group" class:dropup={bottom} role="group" use:blur={closeMenu}>
    <button
        type="button"
        class="local-mail-action-label-button btn dropdown-toggle"
        class:btn-secondary={!bottom}
        class:btn-light={bottom}
        class:disabled={!$store.selectedMessages.size}
        disabled={!$store.selectedMessages.size}
        aria-expanded={expanded}
        title={$store.strings.labels}
        on:click={toggleMenu}
    >
        <i class="fa fa-fw fa-tag" />
    </button>
    {#if expanded}
        <div class="dropdown-menu show">
            {#each $store.labels as label (label.id)}
                <button
                    type="button"
                    class="dropdown-item local-mail-action-label-button-item"
                    on:click={() => toggleLabel(label.id)}
                >
                    <i class="fa fa-fw {labelIconClass(label.id)}" />
                    {label.name}
                </button>
            {/each}
            {#if $store.labels.length > 0}
                <div class="dropdown-divider" />
            {/if}
            {#if applyEnabled}
                <button
                    type="button"
                    class="dropdown-item local-mail-action-label-button-item"
                    on:click={applyLabels}
                >
                    {$store.strings.apply}
                </button>
            {:else}
                <button
                    type="button"
                    class="dropdown-item local-mail-action-label-button-item"
                    on:click={newLabel}
                >
                    {$store.strings.newlabel}
                </button>
            {/if}
        </div>
    {/if}
    {#if $store.params.dialog == 'createlabel'}
        <LabelModal {store} onCancel={() => store.hideDialog()} onSubmit={createLabel} />
    {/if}
</div>

<style>
    .local-mail-action-label-button::after {
        display: none !important;
    }
</style>
