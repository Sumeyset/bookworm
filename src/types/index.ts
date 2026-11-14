export type ShelfStatus = 'want_to_read' | 'reading' | 'read';

export interface User {
  id: string;
  name: string;
  username: string;
  email: string;
  avatar_url?: string;
  bio?: string;
  yearly_reading_goal: number;
  preferred_genres: string[];
  tree_shelf_names?: Record<number, string>;
  onboarding_completed_at?: string | null;
  created_at: string;
}

export interface Book {
  id: string;
  ol_id: string;
  title: string;
  author_name: string;
  cover_url: string;
  first_publish_year?: number;
  description?: string;
  total_pages?: number;
}

export interface UserBookShelf {
  id: string;
  user_id: string;
  book_id: string;
  status: ShelfStatus;
  shelf_slot?: number; // 0 to 6 (7 branches)
  shelf_order?: number; // 0 to 2 (up to 3 books per shelf)
  shelf_name?: string;
  current_page?: number;
  total_pages?: number;
  read_at?: string | null;
  created_at: string;
  updated_at: string;
}

export interface Review {
  id: string;
  user_id: string;
  book_id: string;
  rating: number; // 0.5 to 5.0
  body?: string;
  created_at: string;
  updated_at: string;
}

export interface BookPdf {
  id: string;
  user_id: string;
  book_id: string;
  original_name: string;
  file_url: string; // data URL or object URL or sample PDF
  created_at: string;
}

export interface HighlightRectangle {
  x: number; // 0 to 1
  y: number; // 0 to 1
  width: number; // 0 to 1
  height: number; // 0 to 1
}

export interface BookPdfHighlight {
  id: string;
  user_id: string;
  book_pdf_id: string;
  page_number: number;
  text: string;
  rectangles: HighlightRectangle[];
  created_at: string;
}

export interface QuoteCard {
  id: string;
  user_id: string;
  book_pdf_highlight_id: string;
  share_token: string;
  shared_at?: string | null;
  created_at: string;
  // Denormalized/joined helpers
  quoteText?: string;
  bookTitle?: string;
  bookAuthor?: string;
  userName?: string;
}

export interface DailyQuote {
  text: string;
  author: string;
}
