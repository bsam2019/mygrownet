const storageBase = '/storage/';

export function growmartImage(path: string | null | undefined): string | undefined {
    if (!path) return undefined;
    if (path.startsWith('http://') || path.startsWith('https://')) return path;
    return storageBase + path;
}
