<!--
SPDX-FileCopyrightText: 2023-2024 Proyecto UNIMOODLE <direccion.area.estrategia.digital@uva.es>
SPDX-FileCopyrightText: 2025 Albert Gasset <albertgasset@fsfe.org>

SPDX-License-Identifier: GPL-3.0-or-later
-->
<svelte:options immutable={true} />

<script lang="ts">
    import type { User } from '../lib/state';

    export let user: User | undefined = undefined;
    export let icon = 'fa-user';
</script>

{#if user?.pictureurl}
    <img
        src={user.pictureurl}
        width="35"
        height="35"
        alt=""
        aria-hidden="true"
        class="rounded-circle"
    />
{:else}
    <span class="local-mail-user-picture-fallback m-0">
        {#if user?.firstname || user?.lastname}
            {user.firstname.slice(0, 1)}{user.lastname.slice(0, 1)}
        {:else}
            <i class="fa {icon}" />
        {/if}
    </span>
{/if}

<style>
    .local-mail-user-picture-fallback {
        width: 35px;
        height: 35px;
        background-color: rgba(0, 0, 0, 0.1);
        vertical-align: middle;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        color: var(--dark);
        font-weight: 400;
    }
</style>
