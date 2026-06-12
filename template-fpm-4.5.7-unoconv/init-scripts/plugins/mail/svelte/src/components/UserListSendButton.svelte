<!--
SPDX-FileCopyrightText: 2023-2024 Proyecto UNIMOODLE <direccion.area.estrategia.digital@uva.es>
SPDX-FileCopyrightText: 2025 Albert Gasset <albertgasset@fsfe.org>

SPDX-License-Identifier: GPL-3.0-or-later
-->
<svelte:options immutable={true} />

<script lang="ts">
    import { onDestroy, onMount } from 'svelte';
    import { loadModule, type CorePubSub } from '../lib/amd';
    import type { Course, Strings } from '../lib/state';
    import { createUrl } from '../lib/url';

    export let strings: Strings;
    export let courses: ReadonlyArray<Course>;
    export let userid: number;
    export let courseid: number;
    export let form: HTMLFormElement;

    $: course = courses.find((course) => course.id == courseid);
    let recipients: number[] = [];

    onMount(async () => {
        const pubsub = await loadModule<CorePubSub>('core/pubsub');
        pubsub.subscribe('core/checkbox-toggleall:checkboxToggled', updateRecipients);
        updateRecipients();
    });

    onDestroy(async () => {
        const pubsub = await loadModule<CorePubSub>('core/pubsub');
        pubsub.unsubscribe('core/checkbox-toggleall:checkboxToggled', updateRecipients);
    });

    const updateRecipients = async () => {
        recipients = [];
        const formData = new FormData(form);
        for (const name of formData.keys()) {
            const match = /^user(\d+)$/.exec(name);
            if (match) {
                const id = parseInt(match[1]);
                if (id != userid) {
                    recipients.push(id);
                }
            }
        }
    };

    const createMessage = (role: string) => {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = createUrl(course?.id ?? 0, [], role);
        form.style.display = 'none'

        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'recipients';
        input.value = recipients.join(',');
        form.appendChild(input);

        document.body.appendChild(form);

        form.submit();
    };
</script>

{#if course}
    <div class="btn-group ml-2 my-2">
        <button
            type="button"
            class="btn btn-outline-secondary text-dark bg-white dropdown-toggle"
            data-toggle="dropdown"
            data-bs-toggle="dropdown"
            aria-expanded="false"
            disabled={!recipients.length}
        >
            <i class="fa fa-envelope-o mr-1" aria-hidden="true" />
            {strings.sendmail}
        </button>
        <div class="dropdown-menu">
            {#each ['to', 'cc', 'bcc'] as role (role)}
                <button type="button" class="dropdown-item" on:click={() => createMessage(role)}>
                    {strings[role]}
                </button>
            {/each}
        </div>
    </div>
{/if}
