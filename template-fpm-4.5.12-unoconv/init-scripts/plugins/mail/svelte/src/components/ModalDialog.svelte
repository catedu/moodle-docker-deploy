<!--
SPDX-FileCopyrightText: 2023-2024 Proyecto UNIMOODLE <direccion.area.estrategia.digital@uva.es>
SPDX-FileCopyrightText: 2025 Albert Gasset <albertgasset@fsfe.org>

SPDX-License-Identifier: GPL-3.0-or-later
-->
<svelte:options immutable={true} />

<script lang="ts">
    import { onDestroy, onMount } from 'svelte';
    import { fade, fly } from 'svelte/transition';
    import { blur } from '../actions/blur';

    export let title: string;
    export let cancelText = '';
    export let confirmText = '';
    export let onCancel: () => void;
    export let onConfirm: (() => void) | undefined = undefined;
    export let confirmClass = 'btn-primary';
    export let confirmDisabled = false;

    let node: HTMLElement;

    onMount(() => {
        document.body.classList.add('modal-open');
        node.focus();
    });

    onDestroy(() => {
        document.body.classList.remove('modal-open');
    });

    const handleKey = (event: KeyboardEvent) => {
        if (event.key == 'Escape') {
            onCancel();
        }
    };
</script>

<svelte:body on:keyup={handleKey} />

<div
    class="modal show"
    tabindex="-1"
    role="dialog"
    aria-label={title}
    aria-modal="true"
    bind:this={node}
    transition:fly|global={{ y: -100 }}
    use:blur={onCancel}
>
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header align-items-center">
                <h5 class="modal-title">
                    {title}
                </h5>
                <button
                    type="button"
                    class="close d-flex align-items-center justify-content ml-auto"
                    aria-label={cancelText}
                    on:click={onCancel}
                >
                    <span aria-hidden="true" class="fa fa-times" />
                </button>
            </div>
            <div class="modal-body">
                <slot />
            </div>
            {#if cancelText || (confirmText && onConfirm)}
                <div class="modal-footer">
                    {#if cancelText}
                        <button type="button" class="btn btn-secondary" on:click={onCancel}>
                            {cancelText}
                        </button>
                    {/if}
                    {#if confirmText && onConfirm}
                        <button
                            type="button"
                            class="btn {confirmClass}"
                            disabled={confirmDisabled}
                            on:click={onConfirm}
                        >
                            {confirmText}
                        </button>
                    {/if}
                </div>
            {/if}
        </div>
    </div>
</div>

<div class="modal-backdrop show" transition:fade|global />
