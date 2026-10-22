<!--
SPDX-FileCopyrightText: 2023-2024 Proyecto UNIMOODLE <direccion.area.estrategia.digital@uva.es>
SPDX-FileCopyrightText: 2025 Albert Gasset <albertgasset@fsfe.org>

SPDX-License-Identifier: GPL-3.0-or-later
-->
<svelte:options immutable={true} />

<script lang="ts">
    import { flip } from 'svelte/animate';
    import { fly } from 'svelte/transition';
    import type { Store } from '../lib/store';

    export let store: Store;
</script>

<div
    aria-live="polite"
    aria-atomic="true"
    class="local-mail-toasts position-fixed d-flex flex-column-reverse p-3"
>
    {#each $store.toasts as toast (toast)}
        <div
            animate:flip
            in:fly|global={{ y: 50, delay: 200 }}
            out:fly|global={{ y: 50, duration: 400 }}
            class="toast mt-2 mb-0 show"
            role="alert"
            aria-live="assertive"
            aria-atomic="true"
            data-autohide="false"
        >
            <div class="toast-body d-flex align-items-center p-2">
                <div class="px-1 mr-auto">{toast.text}</div>
                {#if toast.undo}
                    <button
                        type="button"
                        class="local-mail-toast-undo btn font-weight-bold px-2 py-0 ml-3"
                        on:click={() => store.undo(toast)}
                    >
                        {$store.strings.undo}
                    </button>
                {/if}

                <button
                    type="button"
                    class="btn px-2 py-0 ml-2"
                    title={$store.strings.close}
                    on:click={() => store.hideToast(toast)}
                >
                    <i class="fa fa-times" />
                </button>
            </div>
        </div>
    {/each}
</div>

<style>
    .local-mail-toasts {
        left: 0;
        bottom: 0;
        z-index: 1100;
        pointer-events: none;
    }

    .local-mail-toasts .toast {
        opacity: 1;
        pointer-events: auto;
        flex-basis: 0;
        max-width: 400px;
    }

    .local-mail-toast-undo {
        color: var(--activitycontent);
    }
</style>
