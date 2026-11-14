export interface OpenLibrarySearchResult {
  ol_id: string;
  title: string;
  author_name: string;
  first_publish_year?: number;
  cover_url: string;
  rating?: number;
  database_id?: string;
}

const searchCache = new Map<string, OpenLibrarySearchResult[]>();

export const OpenLibraryService = {
  async search(query: string): Promise<OpenLibrarySearchResult[]> {
    const trimmed = query.trim().toLowerCase();
    if (!trimmed) return [];

    if (searchCache.has(trimmed)) {
      return searchCache.get(trimmed)!;
    }

    try {
      const url = `https://openlibrary.org/search.json?q=${encodeURIComponent(trimmed)}&limit=20&fields=key,title,author_name,first_publish_year,cover_i`;
      const response = await fetch(url);
      if (!response.ok) {
        throw new Error(`OpenLibrary request failed: ${response.statusText}`);
      }
      const data = await response.json();
      const docs = data.docs || [];

      const results: OpenLibrarySearchResult[] = docs.map((doc: any) => {
        const ol_id = (doc.key || '').replace('/works/', '');
        const cover_url = doc.cover_i
          ? `https://covers.openlibrary.org/b/id/${doc.cover_i}-M.jpg`
          : 'https://images.unsplash.com/photo-1544947950-fa07a98d237f?auto=format&fit=crop&q=80&w=300';
        return {
          ol_id,
          title: doc.title || 'Untitled',
          author_name: Array.isArray(doc.author_name) ? doc.author_name[0] : (doc.author_name || 'Unknown Author'),
          first_publish_year: doc.first_publish_year,
          cover_url,
        };
      });

      searchCache.set(trimmed, results);
      return results;
    } catch (err) {
      console.warn('OpenLibrary search error (using fallback / cached books):', err);
      throw err;
    }
  },
};
