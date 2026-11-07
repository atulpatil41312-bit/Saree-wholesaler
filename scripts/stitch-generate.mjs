import { mkdir, readFile, writeFile } from 'node:fs/promises';
import path from 'node:path';
import process from 'node:process';
import { stitch } from '@google/stitch-sdk';

function argument(name) {
    const index = process.argv.indexOf(name);

    return index >= 0 ? process.argv[index + 1] : undefined;
}

async function downloadHtml(value) {
    if (!value) {
        return '';
    }

    if (!/^https?:\/\//i.test(value)) {
        return value;
    }

    const response = await fetch(value);

    if (!response.ok) {
        throw new Error(`Unable to download Stitch HTML (${response.status}).`);
    }

    return response.text();
}

async function downloadImage(value) {
    if (!value) {
        return null;
    }

    if (!/^https?:\/\//i.test(value)) {
        return null;
    }

    const response = await fetch(value);

    if (!response.ok) {
        return null;
    }

    return Buffer.from(await response.arrayBuffer());
}

async function main() {
    const projectId = argument('--project-id') || process.env.STITCH_PROJECT_ID;
    const prompt = argument('--prompt');
    const device = argument('--device') || 'DESKTOP';
    const outputDirectory = argument('--output-dir');

    if (!projectId || !prompt || !outputDirectory) {
        throw new Error('Missing Stitch generation arguments.');
    }

    await mkdir(outputDirectory, { recursive: true });

    const project = stitch.project(projectId);
    const screen = await project.generate(prompt, device);
    const html = await downloadHtml(await screen.getHtml());
    const image = await downloadImage(await screen.getImage());

    if (!html.trim()) {
        throw new Error('Stitch returned empty HTML.');
    }

    await writeFile(path.join(outputDirectory, 'screen.html'), html, 'utf8');

    if (image) {
        await writeFile(path.join(outputDirectory, 'screen.png'), image);
    }

    process.stdout.write(JSON.stringify({
        projectId,
        screenId: screen.id,
        hasImage: Boolean(image),
    }));
}

main().catch((error) => {
    process.stderr.write(error instanceof Error ? error.message : String(error));
    process.exitCode = 1;
});
