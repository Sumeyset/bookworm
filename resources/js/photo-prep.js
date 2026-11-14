// Shrink a photo before upload: max 2048px on the long side, as JPEG.
// Keeps uploads small (and under PHP's upload limit) and drops location data.
export async function preparePhoto(file) {
    const source = await createImageBitmap(file, { imageOrientation: 'from-image' }).catch(() => null);
    if (!source) throw new Error(`“${file.name}” isn’t a photo we can open.`);
    const scale = Math.min(1, 2048 / Math.max(source.width, source.height));
    const width = Math.round(source.width * scale);
    const height = Math.round(source.height * scale);
    const canvas = document.createElement('canvas');
    canvas.width = width;
    canvas.height = height;
    const context = canvas.getContext('2d');
    context.fillStyle = '#ffffff';
    context.fillRect(0, 0, width, height);
    context.drawImage(source, 0, 0, width, height);
    source.close?.();
    const blob = await new Promise((resolve) => canvas.toBlob(resolve, 'image/jpeg', 0.88));
    if (!blob) throw new Error(`“${file.name}” couldn’t be prepared.`);
    const name = `${file.name.replace(/\.[^.]+$/, '') || 'photo'}.jpg`;
    return { file: new File([blob], name, { type: 'image/jpeg' }), width, height };
}

