<!--
SPDX-FileCopyrightText: 2023-2024 Proyecto UNIMOODLE <direccion.area.estrategia.digital@uva.es>

SPDX-License-Identifier: GPL-3.0-or-later
-->
<svelte:options immutable={true} />

<script lang="ts">
    import { onMount } from 'svelte';
    import type { Label } from '../lib/state';
    import type { Store } from '../lib/store';
    import { colors, normalizeLabelName } from '../lib/utils';
    import ModalDialog from './ModalDialog.svelte';

    export let store: Store;
    export let label: Label | undefined = undefined;
    export let onCancel: () => void;
    export let onSubmit: (name: string, color: string) => void;

    let nameEl: HTMLElement;

    $: id = `local-mail-label-modal-${label?.id || 'new'}`;
    $: name = label?.name || '';
    $: selectedColor = label?.color || colors[0];
    $: emptyName = normalizeLabelName(name) == '';
    $: repeatedName = $store.labels.some(
        (l) => l.id != label?.id && l.name == normalizeLabelName(name),
    );
    $: validName = !emptyName && !repeatedName;

    const submit = async () => {
        onSubmit(name, selectedColor);
    };

    onMount(() => nameEl.focus());
</script>

<ModalDialog
    title={$store.strings[label ? 'editlabel' : 'newlabel']}
    cancelText={$store.strings.cancel}
    {onCancel}
    confirmText={$store.strings[label ? 'save' : 'create']}
    confirmDisabled={!validName}
    onConfirm={submit}
>
    <form on:submit|preventDefault={submit}>
        <div class="form-group mb-0">
            <label for="local-mail-label-modal-name">{$store.strings.name}</label>
            <input
                type="text"
                required
                class="form-control is-invalid"
                class:is-valid={validName}
                class:is-invalid={!validName}
                id="{id}-name"
                bind:this={nameEl}
                bind:value={name}
            />
            <div class="invalid-feedback">
                {#if repeatedName}
                    {$store.strings.errorrepeatedlabelname}
                {:else}
                    &nbsp;
                {/if}
            </div>
            <div class="valid-feedback">&nbsp;</div>
        </div>
        <div class="form-group">
            <label for="{id}-color">
                {$store.strings.color}
            </label>
            <div role="radiogroup" class="local-mail-label-modal-color" id="{id}-color">
                {#each colors as color (color)}
                    <button
                        role="radio"
                        aria-checked={color == selectedColor}
                        tabindex="0"
                        title={$store.strings[`color${color}`]}
                        class="local-mail-label-modal-color-option btn"
                        style={`color: var(--local-mail-color-${color}-fg, var(--local-mail-color-gray-fg));` +
                            `background-color: var(--local-mail-color-${color}-bg, var(--local-mail-color-gray-bg))`}
                        on:click|preventDefault={() => {
                            selectedColor = color;
                        }}
                    >
                        {#if color == selectedColor}
                            <i class="fa fa-check local-mail-label-modal-color-option-check" />
                        {:else}
                            <span aria-hidden="true">a</span>
                        {/if}
                    </button>
                {/each}
            </div>
        </div>
    </form>
</ModalDialog>

<style>
    .local-mail-label-modal-color {
        display: flex;
        flex-wrap: wrap;
    }
    .local-mail-label-modal-color-option {
        width: 2rem;
        height: 2rem;
        margin-right: 0.5rem;
        margin-bottom: 0.5rem;
        display: flex;
        justify-content: center;
        align-items: center;
        color: var(--local-mail-color-gray-fg);
        background-color: var(--local-mail-color-gray-bg);
    }

    .local-mail-label-modal-color-option:last-child {
        margin-right: 0;
    }
</style>
