<!--
SPDX-FileCopyrightText: 2023-2024 Proyecto UNIMOODLE <direccion.area.estrategia.digital@uva.es>

SPDX-License-Identifier: GPL-3.0-or-later
-->
<svelte:options immutable={true} />

<script lang="ts">
    import type { Store } from '../lib/store';
    import ModalDialog from './ModalDialog.svelte';

    export let store: Store;
    export let onCancel: () => void;

    let markasread = $store.preferences.markasread;
    let notifications = $store.settings.messageprocessors
        .map((processor) => processor.name)
        .filter((name) => $store.preferences.notifications.includes(name));

    const handleConfirm = () => {
        onCancel();
        store.savePreferences({ markasread, notifications });
    };

    const toggleMarkAsRead = () => {
        markasread = !markasread;
    };

    const toggleMessageProcessor = (name: string) => {
        notifications = notifications.includes(name)
            ? notifications.filter((n) => n != name)
            : [...notifications, name];
    };
</script>

<ModalDialog
    title={$store.strings.preferences}
    confirmText={$store.strings.save}
    cancelText={$store.strings.cancel}
    onConfirm={handleConfirm}
    {onCancel}
>
    <h6 class="mb-3">{$store.strings.notifications}</h6>

    {#each $store.settings.messageprocessors as processor}
        <div class="custom-control custom-switch my-2">
            <input
                id="local-mail-preferences-messageprocessor-{processor.name}"
                class="custom-control-input"
                type="checkbox"
                checked={processor.locked
                    ? processor.enabled
                    : notifications.includes(processor.name)}
                disabled={processor.locked}
                on:click={() => toggleMessageProcessor(processor.name)}
            />
            <label
                for="local-mail-preferences-messageprocessor-{processor.name}"
                class="custom-control-label pl-1"
            >
                {processor.displayname}
                {#if processor.locked}
                    <span class="ml-1">({$store.strings.locked})</span>
                {/if}
            </label>
        </div>
    {/each}
    <div class="custom-control custom-switch my-2 mt-3">
        <input
            id="local-mail-preferences-markasread"
            class="custom-control-input"
            type="checkbox"
            checked={markasread && notifications.length > 0}
            disabled={notifications.length == 0}
            on:click={toggleMarkAsRead}
        />
        <label for="local-mail-preferences-markasread" class="custom-control-label pl-1">
            {$store.strings.markmessagesasread}
        </label>
    </div>
</ModalDialog>
