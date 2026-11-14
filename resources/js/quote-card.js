// Builds the same quote card markup as resources/views/components/quote-card.blade.php.

export const CARD_STYLES = [
    { value: 'wine', label: 'Wine' },
    { value: 'paper', label: 'Paper' },
    { value: 'midnight', label: 'Midnight' },
    { value: 'sage', label: 'Sage' },
    { value: 'sunset', label: 'Sunset' },
    { value: 'noir', label: 'Noir' },
    { value: 'blush', label: 'Blush' },
    { value: 'ocean', label: 'Ocean' },
];

export const CARD_FONTS = [
    { value: 'serif', label: 'Serif' },
    { value: 'classic', label: 'Classic' },
    { value: 'hand', label: 'Handwritten' },
    { value: 'sans', label: 'Modern' },
    { value: 'mono', label: 'Typewriter' },
];

export function cardLength(text) {
    const length = [...String(text)].length;
    if (length <= 80) return 's';
    if (length <= 180) return 'm';
    if (length <= 320) return 'l';
    if (length <= 520) return 'xl';
    return 'xxl';
}

export function buildQuoteCard({ text, design = 'wine', font = 'serif', title = '', meta = '', thought = false }) {
    const figure = document.createElement('figure');
    figure.className = `qcard qcard--${design} qcard--f-${font} qcard--len-${cardLength(text)}${thought ? ' qcard--thought' : ''}`;

    const inner = document.createElement('div');
    inner.className = 'qcard__inner';

    const mark = document.createElement('span');
    mark.className = 'qcard__mark';
    mark.setAttribute('aria-hidden', 'true');
    mark.textContent = '“';

    const quote = document.createElement('blockquote');
    quote.className = 'qcard__text';
    quote.textContent = text;

    inner.append(mark, quote);

    if (title || meta) {
        const credit = document.createElement('figcaption');
        credit.className = 'qcard__credit';
        if (title) {
            const titleElement = document.createElement('span');
            titleElement.className = 'qcard__title';
            titleElement.textContent = title;
            credit.append(titleElement);
        }
        if (meta) {
            const metaElement = document.createElement('span');
            metaElement.className = 'qcard__meta';
            metaElement.textContent = meta;
            credit.append(metaElement);
        }
        inner.append(credit);
    }

    const brand = document.createElement('span');
    brand.className = 'qcard__brand';
    brand.setAttribute('aria-hidden', 'true');
    brand.textContent = 'Bookworm';

    figure.append(inner, brand);
    return figure;
}
