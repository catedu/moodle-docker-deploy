<!--
SPDX-FileCopyrightText: 2023-2024 Proyecto UNIMOODLE <direccion.area.estrategia.digital@uva.es>
SPDX-FileCopyrightText: 2025 Albert Gasset <albertgasset@fsfe.org>

SPDX-License-Identifier: GPL-3.0-or-later
-->
<svelte:options immutable={true} />

<script lang="ts">
    import { truncate } from '../actions/truncate';
    import type { Attachment, Reference, Strings } from '../lib/state';
    import { downloadAllUrl } from '../lib/url';
    import { formatSize } from '../lib/utils';

    export let strings: Strings;
    export let message: Reference;

    const fileName = (file: Attachment): string =>
        (file.filepath + file.filename).replace(/^\//, '');
</script>

<div class="local-mail-message-attachments d-sm-flex flex-wrap">
    {#each message.attachments as file}
        <a
            href={file.fileurl}
            class="local-mail-message-attachments-file btn btn-light d-flex align-items-center px-2 py-1 mr-3 mb-3"
        >
            <img
                aria-hidden="true"
                alt={file.filename}
                src={file.iconurl}
                width="24"
                height="24"
                class="flex-shrink-0"
            />
            <i
                class="fa fa-download d-flex justify-content-center align-items-center flex-shrink-0"
                aria-hidden="true"
                style="width: 24px; height: 24px"
            />
            <div class="ml-2 mr-1" style="min-width: 0">
                <div use:truncate={fileName(file)}>{fileName(file)}</div>
                <div class="text-left text-muted">{formatSize(file.filesize)}</div>
            </div>
        </a>
    {/each}
    {#if message.attachments.length > 1}
        <a
            href={downloadAllUrl(message.id)}
            class="btn btn-light d-flex align-items-center p-2 mr-3 mb-3"
        >
            <i
                class="fa fa-download d-flex justify-content-center align-items-center flex-shrink-0"
                aria-hidden="true"
                style="width: 24px; height: 24px"
            />
            <div class="text-truncate ml-2">{strings.downloadall}</div>
        </a>
    {/if}
</div>

<style>
    .local-mail-message-attachments {
        margin-top: 1rem;
        margin-right: -1rem;
    }

    .local-mail-message-attachments a {
        min-width: 0;
        min-height: 3rem;
        line-height: 1.25;
    }

    .local-mail-message-attachments-file:not(:hover) .fa-download {
        display: none !important;
    }
    .local-mail-message-attachments-file:hover img {
        display: none !important;
    }

    @media (min-width: 576px) {
        .local-mail-message-attachments {
            margin-bottom: -1rem;
        }
    }
</style>
