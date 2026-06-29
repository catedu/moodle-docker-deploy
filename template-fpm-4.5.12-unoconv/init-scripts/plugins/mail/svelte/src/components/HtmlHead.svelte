<!--
SPDX-FileCopyrightText: 2023-2024 Proyecto UNIMOODLE <direccion.area.estrategia.digital@uva.es>

SPDX-License-Identifier: GPL-3.0-or-later
-->
<svelte:options immutable={true} />

<script lang="ts">
    import { onDestroy, tick } from 'svelte';
    import { loadModule, type CoreFragment } from '../lib/amd';

    /** Script tags to be added to the head. */
    export let javascript: string | undefined;

    let scriptElement: Element | undefined;

    $: updateJavascript(javascript || '');

    const updateJavascript = async (javascript: string) => {
        const fragment = await loadModule<CoreFragment>('core/fragment');
        scriptElement?.remove();
        scriptElement = undefined;
        await tick();
        scriptElement = document.createElement('script');
        scriptElement.setAttribute('type', 'text/javascript');
        scriptElement.innerHTML = fragment.processCollectedJavascript(javascript);
        document.head.append(scriptElement);
    };

    onDestroy(() => {
        scriptElement?.remove();
    });
</script>
