import React, { useState } from 'react';
import { useAuth } from '../context/AuthContext';
import { Logo } from '../components/Logo';
import { StorageService } from '../services/storage';
import { BookOpen, User, X } from 'lucide-react';

interface AuthModalProps {
  isOpen: boolean;
  initialMode?: 'login' | 'register';
  onClose: () => void;
  onSuccess: () => void;
}

export const AuthModal: React.FC<AuthModalProps> = ({
  isOpen,
  initialMode = 'login',
  onClose,
  onSuccess,
}) => {
  const { login, register, switchUser } = useAuth();
  const [mode, setMode] = useState<'login' | 'register'>(initialMode);
  const [name, setName] = useState('');
  const [username, setUsername] = useState('');
  const [email, setEmail] = useState('');
  const [error, setError] = useState<string | null>(null);

  if (!isOpen) return null;

  const demoUsers = StorageService.getUsers();

  const handleLoginSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    setError(null);
    const success = login(email || username);
    if (success) {
      onSuccess();
      onClose();
    } else {
      setError('Could not find a reader with that handle or email.');
    }
  };

  const handleRegisterSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    setError(null);
    if (!name.trim() || !username.trim() || !email.trim()) {
      setError('Please fill in all fields.');
      return;
    }
    const existing = StorageService.getUserByUsername(username);
    if (existing) {
      setError('That username is already taken. Please choose another.');
      return;
    }
    register(name, username, email);
    onSuccess();
    onClose();
  };

  const handleQuickSwitch = (userId: string) => {
    switchUser(userId);
    onSuccess();
    onClose();
  };

  return (
    <div className="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-xs">
      <div className="relative w-full max-w-md rounded-3xl border border-warmborder bg-paper p-6 sm:p-8 shadow-2xl dark:border-[#4B3035] dark:bg-raised">
        <button
          type="button"
          onClick={onClose}
          className="absolute right-4 top-4 rounded-full p-1 text-tan hover:bg-parchment dark:hover:bg-[#382329]"
          aria-label="Close"
        >
          <X className="h-5 w-5" />
        </button>

        <div className="text-center mb-6">
          <Logo variant="icon" tone="dark" className="mx-auto" />
          <h2 className="mt-3 font-serif text-2xl font-semibold text-ink dark:text-[#f4e9e2]">
            {mode === 'login' ? 'Welcome back to Bookworm' : 'Start your reading shelf'}
          </h2>
          <p className="mt-1 text-xs text-tan dark:text-[#c7a491]">
            {mode === 'login'
              ? 'Enter your reader account or pick a profile below'
              : 'Join a quieter corner of the internet for book lovers'}
          </p>
        </div>

        {error && (
          <div className="mb-4 rounded-xl border border-red-200 bg-red-50 p-2.5 text-xs text-red-700 dark:bg-red-950/40 dark:border-red-900/60 dark:text-red-300">
            {error}
          </div>
        )}

        {/* Quick Demo Reader Profiles */}
        <div className="mb-5 rounded-2xl border border-warmborder/80 bg-parchment/40 p-3 dark:border-[#4B3035] dark:bg-[#201719]/40">
          <p className="text-[10px] font-semibold uppercase tracking-wider text-tan mb-2">
            One-Click Demo Readers:
          </p>
          <div className="grid grid-cols-2 gap-2">
            {demoUsers.map((u) => (
              <button
                key={u.id}
                type="button"
                onClick={() => handleQuickSwitch(u.id)}
                className="flex items-center gap-2 rounded-xl border border-warmborder bg-white dark:bg-[#271519] dark:border-[#4B3035] p-2 text-left hover:border-burgundy transition"
              >
                <div className="h-6 w-6 rounded-full bg-burgundy text-white font-serif font-bold text-xs flex items-center justify-center">
                  {u.name.charAt(0)}
                </div>
                <div className="min-w-0">
                  <p className="text-xs font-semibold text-ink dark:text-[#f4e9e2] truncate">{u.name}</p>
                  <p className="text-[10px] text-tan truncate">@{u.username}</p>
                </div>
              </button>
            ))}
          </div>
        </div>

        {/* Form */}
        {mode === 'login' ? (
          <form onSubmit={handleLoginSubmit} className="space-y-3">
            <div>
              <label className="block text-xs font-semibold text-walnut dark:text-[#d4c2b9] mb-1">
                Username or Email
              </label>
              <input
                type="text"
                placeholder="e.g. sumeyset or sumeysthavalayeva@gmail.com"
                value={email}
                onChange={(e) => setEmail(e.target.value)}
                required
                className="w-full rounded-xl border border-warmborder bg-parchment/30 dark:bg-[#201719] dark:border-[#4B3035] p-2.5 text-xs text-ink dark:text-[#f4e9e2] focus:border-burgundy focus:outline-hidden"
              />
            </div>

            <button
              type="submit"
              className="mt-2 w-full rounded-full bg-burgundy py-2.5 text-xs font-semibold text-[#FFF8EF] shadow-xs hover:bg-burgundy-hover transition"
            >
              Sign In
            </button>
          </form>
        ) : (
          <form onSubmit={handleRegisterSubmit} className="space-y-3">
            <div>
              <label className="block text-xs font-semibold text-walnut dark:text-[#d4c2b9] mb-1">
                Your Name
              </label>
              <input
                type="text"
                placeholder="Jane Austen"
                value={name}
                onChange={(e) => setName(e.target.value)}
                required
                className="w-full rounded-xl border border-warmborder bg-parchment/30 dark:bg-[#201719] dark:border-[#4B3035] p-2.5 text-xs text-ink dark:text-[#f4e9e2] focus:border-burgundy focus:outline-hidden"
              />
            </div>

            <div>
              <label className="block text-xs font-semibold text-walnut dark:text-[#d4c2b9] mb-1">
                Username
              </label>
              <input
                type="text"
                placeholder="janeausten"
                value={username}
                onChange={(e) => setUsername(e.target.value)}
                required
                className="w-full rounded-xl border border-warmborder bg-parchment/30 dark:bg-[#201719] dark:border-[#4B3035] p-2.5 text-xs text-ink dark:text-[#f4e9e2] focus:border-burgundy focus:outline-hidden"
              />
            </div>

            <div>
              <label className="block text-xs font-semibold text-walnut dark:text-[#d4c2b9] mb-1">
                Email
              </label>
              <input
                type="email"
                placeholder="jane@library.org"
                value={email}
                onChange={(e) => setEmail(e.target.value)}
                required
                className="w-full rounded-xl border border-warmborder bg-parchment/30 dark:bg-[#201719] dark:border-[#4B3035] p-2.5 text-xs text-ink dark:text-[#f4e9e2] focus:border-burgundy focus:outline-hidden"
              />
            </div>

            <button
              type="submit"
              className="mt-2 w-full rounded-full bg-burgundy py-2.5 text-xs font-semibold text-[#FFF8EF] shadow-xs hover:bg-burgundy-hover transition"
            >
              Create My Shelf
            </button>
          </form>
        )}

        <div className="mt-5 text-center text-xs text-tan">
          {mode === 'login' ? (
            <p>
              New here?{' '}
              <button
                type="button"
                onClick={() => setMode('register')}
                className="font-semibold text-burgundy dark:text-gold hover:underline"
              >
                Join Bookworm free
              </button>
            </p>
          ) : (
            <p>
              Already have a shelf?{' '}
              <button
                type="button"
                onClick={() => setMode('login')}
                className="font-semibold text-burgundy dark:text-gold hover:underline"
              >
                Log in
              </button>
            </p>
          )}
        </div>
      </div>
    </div>
  );
};
