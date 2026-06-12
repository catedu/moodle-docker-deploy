<!--
SPDX-FileCopyrightText: 2023-2024 Proyecto UNIMOODLE <direccion.area.estrategia.digital@uva.es>
SPDX-FileCopyrightText: 2024-2025 Albert Gasset <albertgasset@fsfe.org>

SPDX-License-Identifier: GPL-3.0-or-later
-->
<svelte:options immutable={true} />

<script lang="ts">
    import { tick } from 'svelte';
    import { blur } from '../actions/blur';
    import { truncate } from '../actions/truncate';

    export let mode: 'input' | 'button' | 'readonly' = 'input';
    export let inputText: string;
    export let inputPlaceholder: string;
    export let leftIconClass: string;
    export let rightIconClass = '';
    export let rightIconLabel = '';
    export let middleIconClass = '';
    export let middleIconLabel = '';
    export let buttonText = '';
    export let buttonClass = '';
    export let readonlyText = '';
    export let readonlyClass = '';
    export let invalid = false;
    export let onFocus: (() => void) | null = null;
    export let onBlur: (() => void) | null = null;
    export let onInput: ((text: string) => void) | null = null;
    export let onEnter: (() => void) | null = null;
    export let onRightIconClick: (() => void) | null = null;
    export let onMiddleIconClick: (() => void) | null = null;
    export let onButtonClick: (() => void) | null = null;

    export const focus = async () => {
        await tick();
        inputNode.focus();
    };

    let node: HTMLElement;
    let inputNode: HTMLInputElement;

    const handleInputKey = (event: KeyboardEvent) => {
        if (event.key == 'Enter') {
            onEnter?.();
        } else if (event.key == 'Escape') {
            inputNode.blur();
            onBlur?.();
        }
    };
</script>

<div bind:this={node} class="local-mail-combo-box" use:blur={() => onBlur?.()}>
    {#if mode == 'input'}
        <input
            type="text"
            class="local-mail-combo-box-input form-control"
            class:local-mail-combo-box-padding-for-one-icon={rightIconClass || middleIconClass}
            class:local-mail-combo-box-padding-for-two-icons={rightIconClass && middleIconClass}
            class:is-invalid={invalid}
            placeholder={inputPlaceholder}
            aria-label={inputPlaceholder}
            bind:this={inputNode}
            bind:value={inputText}
            on:focus={onFocus}
            on:input={() => onInput?.(inputText)}
            on:keyup={handleInputKey}
        />
    {:else if mode == 'button'}
        <button
            type="button"
            class="local-mail-combo-box-button form-control btn {buttonClass}"
            class:local-mail-combo-box-button-default={buttonClass == ''}
            class:local-mail-combo-box-padding-for-one-icon={rightIconClass && !middleIconClass}
            class:local-mail-combo-box-padding-for-two-icons={middleIconClass}
            use:truncate={buttonText}
            on:click={onButtonClick}
        >
            <slot name="buttonContent">
                {buttonText}
            </slot>
        </button>
    {:else}
        <div
            class="local-mail-combo-box-readonly form-control {readonlyClass}"
            class:local-mail-combo-box-readonly-default={readonlyClass == ''}
            use:truncate={readonlyText}
        >
            {readonlyText}
        </div>
    {/if}

    <div class="local-mail-combo-box-left-icon">
        <i class="fa fa-fw {leftIconClass}" aria-hidden="true" />
    </div>

    {#if middleIconClass}
        <button
            type="button"
            class="local-mail-combo-box-middle-icon btn"
            title={middleIconLabel}
            on:click={onMiddleIconClick}
        >
            <i class="fa fa-fw {middleIconClass}" aria-hidden="true" />
        </button>
    {/if}

    {#if rightIconClass}
        <button
            type="button"
            class="local-mail-combo-box-right-icon btn"
            title={rightIconLabel}
            on:click={onRightIconClick}
        >
            <i class="fa fa-fw {rightIconClass}" aria-hidden="true" />
        </button>
    {/if}

    <slot />
</div>

<style>
    .local-mail-combo-box {
        display: flex;
        min-width: 0;
        flex-grow: 1;
        position: relative;
        display: flex;
        scroll-margin: 1rem;
    }

    .local-mail-combo-box-left-icon {
        padding-left: 0.75rem;
        top: 0;
        left: 0;
        position: absolute;
        height: 100%;
        display: flex;
        align-items: center;
    }

    .local-mail-combo-box-input {
        padding-left: 2.5rem;
        padding-right: 0.5rem;
    }

    .local-mail-combo-box-button {
        padding: 0.375rem 0.5rem 0.375rem 2.5rem;
        margin-bottom: 0;
        text-align: left;
    }

    .local-mail-combo-box-readonly {
        padding-left: 2.5rem;
        padding-right: 0.5rem;
        text-align: left;
    }

    .local-mail-combo-box-button-default,
    .local-mail-combo-box-readonly-default {
        border: 1px solid rgba(0, 0, 0, 0.125);
    }

    .local-mail-combo-box-middle-icon {
        position: absolute;
        top: 0;
        right: 2.5rem;
        height: 100%;
        display: flex;
        align-items: center;
        padding-left: 0.5rem;
        padding-right: 0.5rem;
    }

    .local-mail-combo-box-right-icon {
        position: absolute;
        top: 0;
        right: 0;
        height: 100%;
        display: flex;
        align-items: center;
        padding-left: 0.5rem;
        padding-right: 0.5rem;
    }

    .local-mail-combo-box-padding-for-one-icon.form-control {
        padding-right: 2.5rem !important;
    }

    .local-mail-combo-box-padding-for-two-icons.form-control {
        padding-right: 5rem !important;
    }

    .local-mail-combo-box > .form-control.is-invalid {
        background: none;
    }

    .local-mail-combo-box > :global(.dropdown-menu) {
        max-width: calc(100vw / var(--appzoom, 1) - 30px);
    }

    :global(.list-group) > .local-mail-combo-box {
        padding: 0;
    }

    :global(.list-group) > .local-mail-combo-box > .form-control {
        padding-left: 2.75rem !important;
    }

    :global(.list-group) > .local-mail-combo-box .local-mail-combo-box-left-icon {
        padding-left: 1rem;
    }

    :global(.list-group) > .local-mail-combo-box > .form-control {
        font-size: inherit;
        height: auto;
        padding-top: 0.5rem;
        padding-bottom: 0.5rem;
    }

    :global(.local-mail-navbar-popover) .local-mail-combo-box > :global(.dropdown-menu) {
        max-width: 100%;
    }

    :global(.local-mail-navbar-popover) .local-mail-combo-box > .form-control {
        border-radius: 0;
    }

    :global(.list-group) > .local-mail-combo-box:not(:last-child) > .form-control {
        border-bottom-left-radius: 0;
        border-bottom-right-radius: 0;
    }
</style>
