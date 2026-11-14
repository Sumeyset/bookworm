import { Book, BookPdf, BookPdfHighlight, HighlightRectangle, QuoteCard, Review, ShelfStatus, User, UserBookShelf } from '../types';

const STORAGE_KEYS = {
  USERS: 'bookworm_users_v1',
  CURRENT_USER_ID: 'bookworm_current_user_id_v1',
  BOOKS: 'bookworm_books_v1',
  SHELVES: 'bookworm_shelves_v1',
  REVIEWS: 'bookworm_reviews_v1',
  FOLLOWS: 'bookworm_follows_v1', // Array<{ follower_id: string; following_id: string }>
  PDFS: 'bookworm_pdfs_v1',
  HIGHLIGHTS: 'bookworm_highlights_v1',
  QUOTE_CARDS: 'bookworm_quotes_v1',
};

const SEED_BOOKS: Book[] = [
  {
    id: 'b-1',
    ol_id: 'OL66554W',
    title: 'Pride and Prejudice',
    author_name: 'Jane Austen',
    cover_url: 'https://covers.openlibrary.org/b/id/14624446-M.jpg',
    first_publish_year: 1813,
    description: 'A romantic novel following the turbulent relationship between Elizabeth Bennet, the daughter of a country gentleman, and Fitzwilliam Darcy, a rich aristocratic landowner.',
    total_pages: 432,
  },
  {
    id: 'b-2',
    ol_id: 'OL27479W',
    title: 'The Hobbit',
    author_name: 'J. R. R. Tolkien',
    cover_url: 'https://covers.openlibrary.org/b/id/12003528-M.jpg',
    first_publish_year: 1937,
    description: 'Bilbo Baggins is a hobbit who enjoys a comfortable, unambitious life, rarely traveling further than the pantry of his hobbit-hole in Bag End. But his contentment is disturbed when the wizard Gandalf and a company of thirteen dwarves arrive.',
    total_pages: 310,
  },
  {
    id: 'b-3',
    ol_id: 'OL362248W',
    title: 'Little Women',
    author_name: 'Louisa May Alcott',
    cover_url: 'https://covers.openlibrary.org/b/id/10521270-M.jpg',
    first_publish_year: 1868,
    description: 'Follows the lives of four sisters—Meg, Jo, Beth, and Amy March—detailing their passage from childhood to womanhood.',
    total_pages: 449,
  },
  {
    id: 'b-4',
    ol_id: 'OL1168007W',
    title: '1984',
    author_name: 'George Orwell',
    cover_url: 'https://covers.openlibrary.org/b/id/12539702-M.jpg',
    first_publish_year: 1949,
    description: 'Winston Smith toes the Party line, rewriting history to satisfy the Ministry of Truth. With each lie he writes, Winston grows to hate the Party that seeks power for its own sake.',
    total_pages: 328,
  },
  {
    id: 'b-5',
    ol_id: 'OL262758W',
    title: 'The Picture of Dorian Gray',
    author_name: 'Oscar Wilde',
    cover_url: 'https://covers.openlibrary.org/b/id/12715053-M.jpg',
    first_publish_year: 1890,
    description: 'A story of a fashionable young man who sells his soul for eternal youth and beauty, while a hidden portrait bears the sins of his life.',
    total_pages: 254,
  },
  {
    id: 'b-6',
    ol_id: 'OL27448W',
    title: 'Jane Eyre',
    author_name: 'Charlotte Brontë',
    cover_url: 'https://covers.openlibrary.org/b/id/12646274-M.jpg',
    first_publish_year: 1847,
    description: 'An orphaned young woman survives a harsh upbringing and finds work as a governess at mysterious Thornfield Hall, where she falls in love with the enigmatic Mr. Rochester.',
    total_pages: 507,
  },
  {
    id: 'b-7',
    ol_id: 'OL45804W',
    title: 'Frankenstein',
    author_name: 'Mary Shelley',
    cover_url: 'https://covers.openlibrary.org/b/id/12745348-M.jpg',
    first_publish_year: 1818,
    description: 'Victor Frankenstein discovers the secret of imparting life to inanimate matter, with tragic and haunting consequences for his family and himself.',
    total_pages: 280,
  },
  {
    id: 'b-8',
    ol_id: 'OL100344W',
    title: 'The Great Gatsby',
    author_name: 'F. Scott Fitzgerald',
    cover_url: 'https://covers.openlibrary.org/b/id/12644262-M.jpg',
    first_publish_year: 1925,
    description: 'A portrait of the Jazz Age, telling the story of mysterious millionaire Jay Gatsby and his obsession with the beautiful former debutante Daisy Buchanan.',
    total_pages: 180,
  },
];

const SEED_USERS: User[] = [
  {
    id: 'user-sumeyset',
    name: 'Sumeyset',
    username: 'sumeyset',
    email: 'sumeysthavalayeva@gmail.com',
    bio: 'Finding quiet hours for literature, poetry, and thoughtful marginalia.',
    yearly_reading_goal: 24,
    preferred_genres: ['Classics', 'Literary Fiction', 'Poetry'],
    onboarding_completed_at: new Date().toISOString(),
    created_at: '2026-01-15T08:00:00Z',
  },
  {
    id: 'user-mara',
    name: 'Mara Lin',
    username: 'mara',
    email: 'mara@bookworm.reads',
    bio: 'Three books, three good reasons. Always reading with a cup of earl grey.',
    yearly_reading_goal: 30,
    preferred_genres: ['Classics', 'Historical Fiction', 'Essays'],
    onboarding_completed_at: new Date().toISOString(),
    created_at: '2024-05-10T12:00:00Z',
  },
  {
    id: 'user-julian',
    name: 'Julian Vance',
    username: 'julian',
    email: 'julian@bookworm.reads',
    bio: 'Keeper of dog-eared pages and speculative philosophies.',
    yearly_reading_goal: 18,
    preferred_genres: ['Science Fiction', 'Philosophy', 'Dystopian'],
    onboarding_completed_at: new Date().toISOString(),
    created_at: '2025-02-14T09:30:00Z',
  },
  {
    id: 'user-clara',
    name: 'Clara O’Connor',
    username: 'clara',
    email: 'clara@bookworm.reads',
    bio: 'Gothic romance and quiet evening walks through secondhand bookstalls.',
    yearly_reading_goal: 25,
    preferred_genres: ['Gothic', 'Classics', 'Mystery'],
    onboarding_completed_at: new Date().toISOString(),
    created_at: '2025-08-01T15:20:00Z',
  },
];

function getFromStorage<T>(key: string, defaultValue: T): T {
  try {
    const raw = localStorage.getItem(key);
    if (!raw) return defaultValue;
    return JSON.parse(raw) as T;
  } catch (err) {
    console.error(`Error reading ${key} from storage:`, err);
    return defaultValue;
  }
}

function setToStorage<T>(key: string, value: T): void {
  try {
    localStorage.setItem(key, JSON.stringify(value));
  } catch (err) {
    console.error(`Error writing ${key} to storage:`, err);
  }
}

export const StorageService = {
  init() {
    if (!localStorage.getItem(STORAGE_KEYS.USERS)) {
      setToStorage(STORAGE_KEYS.USERS, SEED_USERS);
    }
    if (!localStorage.getItem(STORAGE_KEYS.CURRENT_USER_ID)) {
      setToStorage(STORAGE_KEYS.CURRENT_USER_ID, 'user-sumeyset');
    }
    if (!localStorage.getItem(STORAGE_KEYS.BOOKS)) {
      setToStorage(STORAGE_KEYS.BOOKS, SEED_BOOKS);
    }
    if (!localStorage.getItem(STORAGE_KEYS.SHELVES)) {
      const now = new Date();
      const currentYear = now.getFullYear();
      const seedShelves: UserBookShelf[] = [
        {
          id: 'sh-1',
          user_id: 'user-sumeyset',
          book_id: 'b-1',
          status: 'read',
          current_page: 432,
          total_pages: 432,
          read_at: `${currentYear}-02-10T14:00:00Z`,
          created_at: `${currentYear}-01-05T10:00:00Z`,
          updated_at: `${currentYear}-02-10T14:00:00Z`,
        },
        {
          id: 'sh-2',
          user_id: 'user-sumeyset',
          book_id: 'b-2',
          status: 'reading',
          current_page: 185,
          total_pages: 310,
          read_at: null,
          created_at: `${currentYear}-03-01T10:00:00Z`,
          updated_at: `${currentYear}-03-12T10:00:00Z`,
        },
        {
          id: 'sh-3',
          user_id: 'user-sumeyset',
          book_id: 'b-3',
          status: 'read',
          current_page: 449,
          total_pages: 449,
          read_at: `${currentYear}-01-28T16:00:00Z`,
          created_at: `${currentYear}-01-10T10:00:00Z`,
          updated_at: `${currentYear}-01-28T16:00:00Z`,
        },
        {
          id: 'sh-4',
          user_id: 'user-sumeyset',
          book_id: 'b-5',
          status: 'reading',
          current_page: 120,
          total_pages: 254,
          read_at: null,
          created_at: `${currentYear}-03-04T10:00:00Z`,
          updated_at: `${currentYear}-03-14T10:00:00Z`,
        },
        {
          id: 'sh-5',
          user_id: 'user-sumeyset',
          book_id: 'b-6',
          status: 'want_to_read',
          current_page: 0,
          total_pages: 507,
          read_at: null,
          created_at: `${currentYear}-02-15T10:00:00Z`,
          updated_at: `${currentYear}-02-15T10:00:00Z`,
        },
        {
          id: 'sh-6',
          user_id: 'user-sumeyset',
          book_id: 'b-7',
          status: 'read',
          current_page: 280,
          total_pages: 280,
          read_at: `${currentYear}-03-02T18:00:00Z`,
          created_at: `${currentYear}-02-18T10:00:00Z`,
          updated_at: `${currentYear}-03-02T18:00:00Z`,
        },
        // Mara's shelf
        {
          id: 'sh-m1',
          user_id: 'user-mara',
          book_id: 'b-1',
          status: 'read',
          current_page: 432,
          total_pages: 432,
          read_at: `${currentYear}-01-15T10:00:00Z`,
          created_at: `${currentYear}-01-01T10:00:00Z`,
          updated_at: `${currentYear}-01-15T10:00:00Z`,
        },
        {
          id: 'sh-m2',
          user_id: 'user-mara',
          book_id: 'b-8',
          status: 'read',
          current_page: 180,
          total_pages: 180,
          read_at: `${currentYear}-02-20T10:00:00Z`,
          created_at: `${currentYear}-02-01T10:00:00Z`,
          updated_at: `${currentYear}-02-20T10:00:00Z`,
        },
        {
          id: 'sh-m3',
          user_id: 'user-mara',
          book_id: 'b-2',
          status: 'reading',
          current_page: 90,
          total_pages: 310,
          read_at: null,
          created_at: `${currentYear}-03-01T10:00:00Z`,
          updated_at: `${currentYear}-03-10T10:00:00Z`,
        },
      ];
      setToStorage(STORAGE_KEYS.SHELVES, seedShelves);
    }
    if (!localStorage.getItem(STORAGE_KEYS.REVIEWS)) {
      const seedReviews: Review[] = [
        {
          id: 'r-1',
          user_id: 'user-sumeyset',
          book_id: 'b-1',
          rating: 5,
          body: 'Elizabeth Bennet remains my favorite protagonist in all of literature. The witty dialogue between her and Darcy never fails to brighten the evening.',
          created_at: '2026-02-10T14:30:00Z',
          updated_at: '2026-02-10T14:30:00Z',
        },
        {
          id: 'r-2',
          user_id: 'user-mara',
          book_id: 'b-1',
          rating: 4.5,
          body: 'A true masterclass in character study and social satire. The letter chapter gives me chills every time I reread it.',
          created_at: '2026-01-15T11:00:00Z',
          updated_at: '2026-01-15T11:00:00Z',
        },
        {
          id: 'r-3',
          user_id: 'user-julian',
          book_id: 'b-4',
          rating: 5,
          body: 'Chilling, prophetic, and relentlessly gripping. The concept of doublethink is explored with devastating precision.',
          created_at: '2026-02-28T16:00:00Z',
          updated_at: '2026-02-28T16:00:00Z',
        },
        {
          id: 'r-4',
          user_id: 'user-sumeyset',
          book_id: 'b-7',
          rating: 4.5,
          body: 'Far deeper and more philosophically heartbreaking than most film adaptations suggest. The Creature’s eloquence makes his rejection all the more tragic.',
          created_at: '2026-03-02T18:15:00Z',
          updated_at: '2026-03-02T18:15:00Z',
        },
      ];
      setToStorage(STORAGE_KEYS.REVIEWS, seedReviews);
    }
    if (!localStorage.getItem(STORAGE_KEYS.FOLLOWS)) {
      const seedFollows = [
        { follower_id: 'user-sumeyset', following_id: 'user-mara' },
        { follower_id: 'user-sumeyset', following_id: 'user-julian' },
        { follower_id: 'user-mara', following_id: 'user-sumeyset' },
        { follower_id: 'user-clara', following_id: 'user-sumeyset' },
      ];
      setToStorage(STORAGE_KEYS.FOLLOWS, seedFollows);
    }
    if (!localStorage.getItem(STORAGE_KEYS.PDFS)) {
      const seedPdfs: BookPdf[] = [
        {
          id: 'pdf-1',
          user_id: 'user-sumeyset',
          book_id: 'b-1',
          original_name: 'Pride_and_Prejudice_Austen.pdf',
          file_url: 'https://raw.githubusercontent.com/mozilla/pdf.js/ba2edeae/examples/learning/helloworld.pdf',
          created_at: '2026-02-01T10:00:00Z',
        },
        {
          id: 'pdf-2',
          user_id: 'user-sumeyset',
          book_id: 'b-2',
          original_name: 'The_Hobbit_Companion.pdf',
          file_url: 'https://raw.githubusercontent.com/mozilla/pdf.js/ba2edeae/examples/learning/helloworld.pdf',
          created_at: '2026-03-01T10:00:00Z',
        },
      ];
      setToStorage(STORAGE_KEYS.PDFS, seedPdfs);
    }
    if (!localStorage.getItem(STORAGE_KEYS.HIGHLIGHTS)) {
      const seedHighlights: BookPdfHighlight[] = [
        {
          id: 'hl-1',
          user_id: 'user-sumeyset',
          book_pdf_id: 'pdf-1',
          page_number: 1,
          text: 'It is a truth universally acknowledged, that a single man in possession of a good fortune, must be in want of a wife.',
          rectangles: [{ x: 0.12, y: 0.15, width: 0.76, height: 0.06 }],
          created_at: '2026-02-02T11:00:00Z',
        },
        {
          id: 'hl-2',
          user_id: 'user-sumeyset',
          book_pdf_id: 'pdf-1',
          page_number: 1,
          text: 'I declare after all there is no enjoyment like reading! How much sooner one tires of any thing than of a book!',
          rectangles: [{ x: 0.15, y: 0.32, width: 0.70, height: 0.08 }],
          created_at: '2026-02-05T15:20:00Z',
        },
      ];
      setToStorage(STORAGE_KEYS.HIGHLIGHTS, seedHighlights);
    }
    if (!localStorage.getItem(STORAGE_KEYS.QUOTE_CARDS)) {
      const seedQuotes: QuoteCard[] = [
        {
          id: 'qc-1',
          user_id: 'user-sumeyset',
          book_pdf_highlight_id: 'hl-1',
          share_token: 'austens-truth-token-9bf4bd48',
          shared_at: '2026-02-03T12:00:00Z',
          created_at: '2026-02-03T12:00:00Z',
          quoteText: 'It is a truth universally acknowledged, that a single man in possession of a good fortune, must be in want of a wife.',
          bookTitle: 'Pride and Prejudice',
          bookAuthor: 'Jane Austen',
          userName: 'Sumeyset',
        },
        {
          id: 'qc-2',
          user_id: 'user-sumeyset',
          book_pdf_highlight_id: 'hl-2',
          share_token: 'reading-joy-token-713946f3',
          shared_at: '2026-02-06T14:00:00Z',
          created_at: '2026-02-06T14:00:00Z',
          quoteText: 'I declare after all there is no enjoyment like reading! How much sooner one tires of any thing than of a book!',
          bookTitle: 'Pride and Prejudice',
          bookAuthor: 'Jane Austen',
          userName: 'Sumeyset',
        },
      ];
      setToStorage(STORAGE_KEYS.QUOTE_CARDS, seedQuotes);
    }
  },

  // USERS
  getUsers(): User[] {
    return getFromStorage<User[]>(STORAGE_KEYS.USERS, SEED_USERS);
  },

  getCurrentUser(): User | null {
    const userId = getFromStorage<string | null>(STORAGE_KEYS.CURRENT_USER_ID, null);
    if (!userId) return null;
    return this.getUserById(userId) || null;
  },

  setCurrentUser(userId: string | null): void {
    setToStorage(STORAGE_KEYS.CURRENT_USER_ID, userId);
  },

  getUserById(id: string): User | undefined {
    return this.getUsers().find((u) => u.id === id);
  },

  getUserByUsername(username: string): User | undefined {
    return this.getUsers().find((u) => u.username.toLowerCase() === username.toLowerCase());
  },

  updateUser(id: string, updates: Partial<User>): User {
    const users = this.getUsers();
    const idx = users.findIndex((u) => u.id === id);
    if (idx === -1) throw new Error('User not found');
    users[idx] = { ...users[idx], ...updates };
    setToStorage(STORAGE_KEYS.USERS, users);
    return users[idx];
  },

  addUser(user: User): void {
    const users = this.getUsers();
    users.push(user);
    setToStorage(STORAGE_KEYS.USERS, users);
  },

  // BOOKS
  getBooks(): Book[] {
    return getFromStorage<Book[]>(STORAGE_KEYS.BOOKS, SEED_BOOKS);
  },

  getBookById(id: string): Book | undefined {
    return this.getBooks().find((b) => b.id === id);
  },

  getBookByOlId(olId: string): Book | undefined {
    return this.getBooks().find((b) => b.ol_id === olId);
  },

  saveOrUpdateBook(bookData: Omit<Book, 'id'> & { id?: string }): Book {
    const books = this.getBooks();
    const existingIndex = books.findIndex((b) => (bookData.id && b.id === bookData.id) || (bookData.ol_id && b.ol_id === bookData.ol_id));
    if (existingIndex >= 0) {
      books[existingIndex] = { ...books[existingIndex], ...bookData };
      setToStorage(STORAGE_KEYS.BOOKS, books);
      return books[existingIndex];
    }
    const newBook: Book = {
      id: bookData.id || `book-${Date.now()}-${Math.random().toString(36).substring(2, 7)}`,
      ...bookData,
    };
    books.push(newBook);
    setToStorage(STORAGE_KEYS.BOOKS, books);
    return newBook;
  },

  // SHELVES
  getShelves(): UserBookShelf[] {
    return getFromStorage<UserBookShelf[]>(STORAGE_KEYS.SHELVES, []);
  },

  getUserShelf(userId: string): (UserBookShelf & { book: Book; rating?: number; review_body?: string })[] {
    const shelves = this.getShelves().filter((s) => s.user_id === userId);
    const books = this.getBooks();
    const reviews = this.getReviews().filter((r) => r.user_id === userId);

    return shelves.map((shelf) => {
      const book = books.find((b) => b.id === shelf.book_id) || {
        id: shelf.book_id,
        ol_id: 'unknown',
        title: 'Unknown Title',
        author_name: 'Unknown Author',
        cover_url: '',
      };
      const review = reviews.find((r) => r.book_id === shelf.book_id);
      return {
        ...shelf,
        book,
        rating: review?.rating,
        review_body: review?.body,
      };
    });
  },

  updateShelfStatus(userId: string, bookId: string, status: ShelfStatus, currentPage?: number, totalPages?: number): UserBookShelf {
    const shelves = this.getShelves();
    const existingIndex = shelves.findIndex((s) => s.user_id === userId && s.book_id === bookId);
    const now = new Date().toISOString();

    if (existingIndex >= 0) {
      const existing = shelves[existingIndex];
      const isNowRead = status === 'read';
      const readAt = isNowRead ? (existing.read_at || now) : null;
      shelves[existingIndex] = {
        ...existing,
        status,
        current_page: currentPage !== undefined ? currentPage : existing.current_page,
        total_pages: totalPages !== undefined ? totalPages : existing.total_pages,
        read_at: readAt,
        updated_at: now,
      };
      setToStorage(STORAGE_KEYS.SHELVES, shelves);
      return shelves[existingIndex];
    } else {
      const newShelf: UserBookShelf = {
        id: `sh-${Date.now()}-${Math.random().toString(36).substring(2, 6)}`,
        user_id: userId,
        book_id: bookId,
        status,
        current_page: currentPage || 0,
        total_pages: totalPages,
        read_at: status === 'read' ? now : null,
        created_at: now,
        updated_at: now,
      };
      shelves.push(newShelf);
      setToStorage(STORAGE_KEYS.SHELVES, shelves);
      return newShelf;
    }
  },

  getTreeShelfNames(userId: string): Record<number, string> {
    const defaultNames: Record<number, string> = {
      0: 'Fav Thrillers',
      1: 'Currently Reading',
      2: 'All-Time Favorites',
      3: 'Cozy Mystery',
      4: 'Want to Read Next',
      5: 'Non-Fiction & Life',
      6: 'Poetry & Bedside Reads',
      7: 'Canopy Classics',
      8: 'Sci-Fi & Futures',
      9: 'Ancient Wisdom',
      10: 'Starry Night Tales',
      11: 'Crown Manuscripts',
    };
    const key = `bookworm_shelf_titles_${userId}`;
    const saved = getFromStorage<Record<number, string>>(key, {});
    return { ...defaultNames, ...saved };
  },

  setTreeShelfTitle(userId: string, slot: number, title: string): Record<number, string> {
    const current = this.getTreeShelfNames(userId);
    current[slot] = title.trim();
    const key = `bookworm_shelf_titles_${userId}`;
    setToStorage(key, current);
    return current;
  },

  assignBookToShelfSlot(userId: string, bookId: string, slot: number, order?: number): { success: boolean; error?: string } {
    const shelves = this.getShelves();
    const currentSlotBooks = shelves.filter((s) => s.user_id === userId && s.shelf_slot === slot && s.book_id !== bookId);
    if (currentSlotBooks.length >= 3) {
      return { success: false, error: 'This branch shelf is full (maximum 3 books allowed).' };
    }

    const existingIndex = shelves.findIndex((s) => s.user_id === userId && s.book_id === bookId);
    const now = new Date().toISOString();
    if (existingIndex >= 0) {
      shelves[existingIndex].shelf_slot = slot;
      shelves[existingIndex].shelf_order = order !== undefined ? order : currentSlotBooks.length;
      shelves[existingIndex].updated_at = now;
    } else {
      shelves.push({
        id: `sh-${Date.now()}-${Math.random().toString(36).substring(2, 6)}`,
        user_id: userId,
        book_id: bookId,
        status: slot === 1 ? 'reading' : 'want_to_read',
        shelf_slot: slot,
        shelf_order: order !== undefined ? order : currentSlotBooks.length,
        created_at: now,
        updated_at: now,
      });
    }
    setToStorage(STORAGE_KEYS.SHELVES, shelves);
    return { success: true };
  },

  moveOrSwapBook(userId: string, sourceBookId: string, targetSlot: number, targetBookId?: string): { success: boolean; error?: string } {
    const shelves = this.getShelves();
    const sourceIndex = shelves.findIndex((s) => s.user_id === userId && s.book_id === sourceBookId);
    if (sourceIndex < 0) return { success: false, error: 'Source book not found.' };

    const now = new Date().toISOString();

    if (targetBookId && targetBookId !== sourceBookId) {
      // Swapping two books
      const targetIndex = shelves.findIndex((s) => s.user_id === userId && s.book_id === targetBookId);
      if (targetIndex >= 0) {
        const oldSourceSlot = shelves[sourceIndex].shelf_slot ?? 0;
        const oldSourceOrder = shelves[sourceIndex].shelf_order ?? 0;

        shelves[sourceIndex].shelf_slot = shelves[targetIndex].shelf_slot;
        shelves[sourceIndex].shelf_order = shelves[targetIndex].shelf_order;
        shelves[sourceIndex].updated_at = now;

        shelves[targetIndex].shelf_slot = oldSourceSlot;
        shelves[targetIndex].shelf_order = oldSourceOrder;
        shelves[targetIndex].updated_at = now;

        setToStorage(STORAGE_KEYS.SHELVES, shelves);
        return { success: true };
      }
    }

    return this.assignBookToShelfSlot(userId, sourceBookId, targetSlot);
  },

  removeBookFromSlot(userId: string, bookId: string): void {
    const shelves = this.getShelves();
    const filtered = shelves.filter((s) => !(s.user_id === userId && s.book_id === bookId));
    setToStorage(STORAGE_KEYS.SHELVES, filtered);
  },

  // REVIEWS & RATINGS
  getReviews(): Review[] {
    return getFromStorage<Review[]>(STORAGE_KEYS.REVIEWS, []);
  },

  getBookReviews(bookId: string): (Review & { user?: User })[] {
    const reviews = this.getReviews().filter((r) => r.book_id === bookId);
    const users = this.getUsers();
    return reviews
      .sort((a, b) => new Date(b.created_at).getTime() - new Date(a.created_at).getTime())
      .map((r) => ({
        ...r,
        user: users.find((u) => u.id === r.user_id),
      }));
  },

  saveReview(userId: string, bookId: string, rating: number, body?: string): Review {
    const reviews = this.getReviews();
    const existingIndex = reviews.findIndex((r) => r.user_id === userId && r.book_id === bookId);
    const now = new Date().toISOString();

    let savedReview: Review;
    if (existingIndex >= 0) {
      reviews[existingIndex] = {
        ...reviews[existingIndex],
        rating,
        body: body !== undefined ? body : reviews[existingIndex].body,
        updated_at: now,
      };
      savedReview = reviews[existingIndex];
    } else {
      savedReview = {
        id: `r-${Date.now()}-${Math.random().toString(36).substring(2, 6)}`,
        user_id: userId,
        book_id: bookId,
        rating,
        body: body || '',
        created_at: now,
        updated_at: now,
      };
      reviews.push(savedReview);
    }
    setToStorage(STORAGE_KEYS.REVIEWS, reviews);

    // Also mark shelf item as read
    this.updateShelfStatus(userId, bookId, 'read');

    return savedReview;
  },

  deleteReview(reviewId: string): void {
    const reviews = this.getReviews().filter((r) => r.id !== reviewId);
    setToStorage(STORAGE_KEYS.REVIEWS, reviews);
  },

  // FOLLOWS
  getFollows(): { follower_id: string; following_id: string }[] {
    return getFromStorage<{ follower_id: string; following_id: string }[]>(STORAGE_KEYS.FOLLOWS, []);
  },

  isFollowing(followerId: string, followingId: string): boolean {
    return this.getFollows().some((f) => f.follower_id === followerId && f.following_id === followingId);
  },

  toggleFollow(followerId: string, followingId: string): boolean {
    const follows = this.getFollows();
    const index = follows.findIndex((f) => f.follower_id === followerId && f.following_id === followingId);
    if (index >= 0) {
      follows.splice(index, 1);
      setToStorage(STORAGE_KEYS.FOLLOWS, follows);
      return false; // unfollowed
    } else {
      follows.push({ follower_id: followerId, following_id: followingId });
      setToStorage(STORAGE_KEYS.FOLLOWS, follows);
      return true; // followed
    }
  },

  getFollowersCount(userId: string): number {
    return this.getFollows().filter((f) => f.following_id === userId).length;
  },

  getFollowingCount(userId: string): number {
    return this.getFollows().filter((f) => f.follower_id === userId).length;
  },

  getFeedReviews(userId: string): (Review & { user: User; book: Book })[] {
    const follows = this.getFollows().filter((f) => f.follower_id === userId);
    const followedIds = new Set(follows.map((f) => f.following_id));
    followedIds.add(userId); // include own updates too

    const reviews = this.getReviews().filter((r) => followedIds.has(r.user_id));
    const users = this.getUsers();
    const books = this.getBooks();

    return reviews
      .sort((a, b) => new Date(b.created_at).getTime() - new Date(a.created_at).getTime())
      .map((r) => ({
        ...r,
        user: users.find((u) => u.id === r.user_id)!,
        book: books.find((b) => b.id === r.book_id)!,
      }))
      .filter((item) => item.user && item.book);
  },

  // PDFS
  getPdfs(userId: string, bookId: string): BookPdf[] {
    const pdfs = getFromStorage<BookPdf[]>(STORAGE_KEYS.PDFS, []);
    return pdfs.filter((p) => p.user_id === userId && p.book_id === bookId);
  },

  addPdf(userId: string, bookId: string, originalName: string, fileUrl: string): BookPdf {
    const pdfs = getFromStorage<BookPdf[]>(STORAGE_KEYS.PDFS, []);
    const newPdf: BookPdf = {
      id: `pdf-${Date.now()}-${Math.random().toString(36).substring(2, 6)}`,
      user_id: userId,
      book_id: bookId,
      original_name: originalName,
      file_url: fileUrl,
      created_at: new Date().toISOString(),
    };
    pdfs.push(newPdf);
    setToStorage(STORAGE_KEYS.PDFS, pdfs);
    return newPdf;
  },

  deletePdf(pdfId: string): void {
    const pdfs = getFromStorage<BookPdf[]>(STORAGE_KEYS.PDFS, []).filter((p) => p.id !== pdfId);
    setToStorage(STORAGE_KEYS.PDFS, pdfs);
  },

  // HIGHLIGHTS
  getHighlights(bookPdfId: string): BookPdfHighlight[] {
    const highlights = getFromStorage<BookPdfHighlight[]>(STORAGE_KEYS.HIGHLIGHTS, []);
    return highlights.filter((h) => h.book_pdf_id === bookPdfId);
  },

  addHighlight(userId: string, bookPdfId: string, pageNumber: number, text: string, rectangles: HighlightRectangle[]): BookPdfHighlight {
    const highlights = getFromStorage<BookPdfHighlight[]>(STORAGE_KEYS.HIGHLIGHTS, []);
    const newHighlight: BookPdfHighlight = {
      id: `hl-${Date.now()}-${Math.random().toString(36).substring(2, 6)}`,
      user_id: userId,
      book_pdf_id: bookPdfId,
      page_number: pageNumber,
      text,
      rectangles,
      created_at: new Date().toISOString(),
    };
    highlights.push(newHighlight);
    setToStorage(STORAGE_KEYS.HIGHLIGHTS, highlights);
    return newHighlight;
  },

  deleteHighlight(highlightId: string): void {
    const highlights = getFromStorage<BookPdfHighlight[]>(STORAGE_KEYS.HIGHLIGHTS, []).filter((h) => h.id !== highlightId);
    setToStorage(STORAGE_KEYS.HIGHLIGHTS, highlights);
  },

  // QUOTES
  getQuoteCards(): QuoteCard[] {
    return getFromStorage<QuoteCard[]>(STORAGE_KEYS.QUOTE_CARDS, []);
  },

  getUserQuoteCards(userId: string): QuoteCard[] {
    return this.getQuoteCards().filter((q) => q.user_id === userId);
  },

  getBookQuotes(bookId: string): QuoteCard[] {
    const allQuotes = this.getQuoteCards();
    const pdfs = getFromStorage<BookPdf[]>(STORAGE_KEYS.PDFS, []).filter((p) => p.book_id === bookId);
    const pdfIds = new Set(pdfs.map((p) => p.id));
    const highlights = getFromStorage<BookPdfHighlight[]>(STORAGE_KEYS.HIGHLIGHTS, []).filter((h) => pdfIds.has(h.book_pdf_id));
    const highlightIds = new Set(highlights.map((h) => h.id));

    return allQuotes.filter((q) => highlightIds.has(q.book_pdf_highlight_id));
  },

  getQuoteByShareToken(token: string): QuoteCard | undefined {
    return this.getQuoteCards().find((q) => q.share_token === token);
  },

  createQuoteCard(userId: string, highlightId: string): QuoteCard {
    const quotes = this.getQuoteCards();
    const existing = quotes.find((q) => q.book_pdf_highlight_id === highlightId);
    if (existing) return existing;

    const highlights = getFromStorage<BookPdfHighlight[]>(STORAGE_KEYS.HIGHLIGHTS, []);
    const highlight = highlights.find((h) => h.id === highlightId);
    const pdfs = getFromStorage<BookPdf[]>(STORAGE_KEYS.PDFS, []);
    const pdf = highlight ? pdfs.find((p) => p.id === highlight.book_pdf_id) : undefined;
    const books = this.getBooks();
    const book = pdf ? books.find((b) => b.id === pdf.book_id) : undefined;
    const user = this.getUserById(userId);

    const newQuote: QuoteCard = {
      id: `qc-${Date.now()}-${Math.random().toString(36).substring(2, 6)}`,
      user_id: userId,
      book_pdf_highlight_id: highlightId,
      share_token: `quote-${Math.random().toString(36).substring(2, 10)}`,
      shared_at: new Date().toISOString(),
      created_at: new Date().toISOString(),
      quoteText: highlight?.text || 'A beautiful line from the book.',
      bookTitle: book?.title || 'Unknown Title',
      bookAuthor: book?.author_name || 'Unknown Author',
      userName: user?.name || 'A Reader',
    };
    quotes.push(newQuote);
    setToStorage(STORAGE_KEYS.QUOTE_CARDS, quotes);
    return newQuote;
  },

  toggleShareQuote(quoteId: string): QuoteCard {
    const quotes = this.getQuoteCards();
    const idx = quotes.findIndex((q) => q.id === quoteId);
    if (idx === -1) throw new Error('Quote not found');
    const isShared = Boolean(quotes[idx].shared_at);
    quotes[idx].shared_at = isShared ? null : new Date().toISOString();
    setToStorage(STORAGE_KEYS.QUOTE_CARDS, quotes);
    return quotes[idx];
  },

  deleteQuote(quoteId: string): void {
    const quotes = this.getQuoteCards().filter((q) => q.id !== quoteId);
    setToStorage(STORAGE_KEYS.QUOTE_CARDS, quotes);
  },
};

// Initialize seed data on load
StorageService.init();
