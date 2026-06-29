<!--
SPDX-FileCopyrightText: 2025 Albert Gasset <albertgasset@fsfe.org>

SPDX-License-Identifier: GPL-3.0-or-later
-->
<svelte:options immutable={true} />

<script lang="ts">
    import type { User } from '../lib/state';

    export let user: User;
</script>

{#if user.profileurl}
    <a href={user.profileurl}>
        {user.fullname}
    </a>
{:else}
    <span>
        {user.fullname}
    </span>
{/if}
