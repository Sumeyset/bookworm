import React, { useState, useEffect } from 'react';
import { useAuth } from './context/AuthContext';
import { Navbar } from './components/Navbar';
import { LandingPage } from './pages/LandingPage';
import { WelcomeHome } from './pages/WelcomeHome';
import { BooksExplore } from './pages/BooksExplore';
import { BookDetail } from './pages/BookDetail';
import { ProfileBookshelf } from './pages/ProfileBookshelf';
import { FeedPage } from './pages/FeedPage';
import { ReadersDirectory } from './pages/ReadersDirectory';
import { PublicQuotePage } from './pages/PublicQuotePage';
import { AuthModal } from './pages/AuthModal';
import { OnboardingModal } from './components/OnboardingModal';

export const App: React.FC = () => {
  const { user } = useAuth();
  const [currentView, setCurrentView] = useState<string>(() => (user ? 'home' : 'landing'));
  const [selectedBookId, setSelectedBookId] = useState<string>('b-1');
  const [targetUsername, setTargetUsername] = useState<string>(user?.username || 'sumeyset');
  const [searchParam, setSearchParam] = useState<string>('');
  const [quoteToken, setQuoteToken] = useState<string>('');
  const [authModalOpen, setAuthModalOpen] = useState(false);
  const [authMode, setAuthMode] = useState<'login' | 'register'>('login');
  const [onboardingOpen, setOnboardingOpen] = useState(false);

  // Sync hash routing on load and changes
  useEffect(() => {
    const parseHash = () => {
      const hash = window.location.hash.replace('#', '');
      if (hash.startsWith('quote=')) {
        const token = hash.replace('quote=', '');
        setQuoteToken(token);
        setCurrentView('quote-public');
      } else if (hash.startsWith('book=')) {
        const id = hash.replace('book=', '');
        setSelectedBookId(id);
        setCurrentView('book-detail');
      } else if (hash.startsWith('user=')) {
        const uname = hash.replace('user=', '');
        setTargetUsername(uname);
        setCurrentView('profile');
      } else if (hash === 'books') {
        setCurrentView('books');
      } else if (hash === 'feed') {
        setCurrentView('feed');
      } else if (hash === 'users') {
        setCurrentView('users');
      }
    };

    parseHash();
    window.addEventListener('hashchange', parseHash);
    return () => window.removeEventListener('hashchange', parseHash);
  }, []);

  const handleNavigate = (view: string, param?: string) => {
    if (view === 'login') {
      setAuthMode('login');
      setAuthModalOpen(true);
      return;
    }
    if (view === 'register') {
      setAuthMode('register');
      setAuthModalOpen(true);
      return;
    }
    if (view === 'onboarding') {
      setOnboardingOpen(true);
      return;
    }

    if (view === 'book-detail' && param) {
      setSelectedBookId(param);
      window.location.hash = `book=${param}`;
    } else if (view === 'profile') {
      const uname = param || user?.username || 'sumeyset';
      setTargetUsername(uname);
      window.location.hash = `user=${uname}`;
    } else if (view === 'books') {
      setSearchParam(param || '');
      window.location.hash = 'books';
    } else if (view === 'feed') {
      window.location.hash = 'feed';
    } else if (view === 'users') {
      window.location.hash = 'users';
    } else if (view === 'home') {
      window.location.hash = '';
    } else if (view === 'landing') {
      window.location.hash = '';
    }

    setCurrentView(view);
    window.scrollTo({ top: 0, behavior: 'smooth' });
  };

  return (
    <div className="min-h-screen bg-ivory text-ink dark:bg-wineblack dark:text-[#F4E9E2] flex flex-col font-sans transition-colors duration-200">
      {/* Navigation Bar */}
      <Navbar
        currentView={currentView}
        onNavigate={handleNavigate}
        onSearchSubmit={(q) => handleNavigate('books', q)}
      />

      {/* Main View Router */}
      <main className="flex-1 paper-grain">
        {currentView === 'landing' && (
          <LandingPage
            onJoin={() => {
              setAuthMode('register');
              setAuthModalOpen(true);
            }}
            onLogin={() => {
              setAuthMode('login');
              setAuthModalOpen(true);
            }}
            onExplore={() => handleNavigate('books')}
          />
        )}

        {currentView === 'home' && (
          <WelcomeHome
            onExploreBooks={() => handleNavigate('books')}
            onOpenMyShelf={() => handleNavigate('profile', user?.username)}
            onOpenOnboarding={() => setOnboardingOpen(true)}
            onSelectBook={(bId) => handleNavigate('book-detail', bId)}
          />
        )}

        {currentView === 'books' && (
          <BooksExplore
            initialQuery={searchParam}
            onSelectBook={(bId) => handleNavigate('book-detail', bId)}
          />
        )}

        {currentView === 'book-detail' && (
          <BookDetail
            bookId={selectedBookId}
            onBack={() => handleNavigate('books')}
            onViewReaderProfile={(uname) => handleNavigate('profile', uname)}
          />
        )}

        {currentView === 'profile' && (
          <ProfileBookshelf
            username={targetUsername}
            onSelectBook={(bId) => handleNavigate('book-detail', bId)}
            onOpenOnboarding={() => setOnboardingOpen(true)}
            onExploreBooks={() => handleNavigate('books')}
          />
        )}

        {currentView === 'feed' && (
          <FeedPage
            onSelectBook={(bId) => handleNavigate('book-detail', bId)}
            onViewReaderProfile={(uname) => handleNavigate('profile', uname)}
            onExploreReaders={() => handleNavigate('users')}
          />
        )}

        {currentView === 'users' && (
          <ReadersDirectory
            onViewReaderProfile={(uname) => handleNavigate('profile', uname)}
          />
        )}

        {currentView === 'quote-public' && (
          <PublicQuotePage
            shareToken={quoteToken}
            onExploreBooks={() => handleNavigate('books')}
            onHome={() => handleNavigate(user ? 'home' : 'landing')}
          />
        )}
      </main>

      {/* Global Modals */}
      <AuthModal
        isOpen={authModalOpen}
        initialMode={authMode}
        onClose={() => setAuthModalOpen(false)}
        onSuccess={() => handleNavigate('home')}
      />

      <OnboardingModal
        isOpen={onboardingOpen}
        onClose={() => setOnboardingOpen(false)}
      />
    </div>
  );
};
