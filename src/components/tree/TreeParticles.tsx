import React, { useEffect, useRef } from 'react';
import { TreeMood } from './TreeAmbience';

interface TreeParticlesProps {
  mood: TreeMood;
  enabled?: boolean;
}

export const TreeParticles: React.FC<TreeParticlesProps> = ({ mood, enabled = true }) => {
  const canvasRef = useRef<HTMLCanvasElement | null>(null);

  useEffect(() => {
    if (!enabled) return;
    const canvas = canvasRef.current;
    if (!canvas) return;
    const ctx = canvas.getContext('2d');
    if (!ctx) return;

    let animId: number;
    let width = (canvas.width = canvas.parentElement?.clientWidth || 800);
    let height = (canvas.height = canvas.parentElement?.clientHeight || 600);

    const handleResize = () => {
      if (!canvas || !canvas.parentElement) return;
      width = canvas.width = canvas.parentElement.clientWidth;
      height = canvas.height = canvas.parentElement.clientHeight;
    };
    window.addEventListener('resize', handleResize);

    // Particle types based on mood
    const particleCount = mood === 'midnight' ? 24 : 16;
    const particles = Array.from({ length: particleCount }).map(() => ({
      x: Math.random() * width,
      y: Math.random() * height,
      size: Math.random() * 4 + 2,
      vx: (Math.random() - 0.5) * 0.6,
      vy: mood === 'midnight' ? (Math.random() - 0.5) * 0.4 : Math.random() * 0.8 + 0.3,
      alpha: Math.random() * 0.7 + 0.2,
      alphaSpeed: (Math.random() * 0.02 + 0.01) * (Math.random() > 0.5 ? 1 : -1),
      rotation: Math.random() * 360,
      rotSpeed: (Math.random() - 0.5) * 2,
    }));

    const render = () => {
      ctx.clearRect(0, 0, width, height);

      particles.forEach((p) => {
        p.x += p.vx;
        p.y += p.vy;
        p.rotation += p.rotSpeed;
        p.alpha += p.alphaSpeed;
        if (p.alpha <= 0.15 || p.alpha >= 0.85) p.alphaSpeed *= -1;

        // Wrap around boundaries
        if (p.y > height + 10) {
          p.y = -10;
          p.x = Math.random() * width;
        }
        if (p.x < -10) p.x = width + 10;
        if (p.x > width + 10) p.x = -10;

        ctx.save();
        ctx.translate(p.x, p.y);
        ctx.rotate((p.rotation * Math.PI) / 180);
        ctx.globalAlpha = p.alpha;

        if (mood === 'autumn') {
          // Fall leaves: amber or crimson
          ctx.fillStyle = p.size > 4 ? '#C94A29' : '#D9822B';
          ctx.beginPath();
          ctx.ellipse(0, 0, p.size * 1.5, p.size * 0.8, 0, 0, Math.PI * 2);
          ctx.fill();
        } else if (mood === 'spring') {
          // Cherry blossom petals: blush pink
          ctx.fillStyle = '#F4A7B9';
          ctx.beginPath();
          ctx.ellipse(0, 0, p.size * 1.3, p.size * 0.7, 0, 0, Math.PI * 2);
          ctx.fill();
        } else if (mood === 'midnight') {
          // Glowing fireflies: soft yellow/cyan orb with radial blur
          const grad = ctx.createRadialGradient(0, 0, 0, 0, 0, p.size * 2);
          grad.addColorStop(0, '#FEF08A');
          grad.addColorStop(0.5, 'rgba(253, 224, 71, 0.4)');
          grad.addColorStop(1, 'rgba(253, 224, 71, 0)');
          ctx.fillStyle = grad;
          ctx.beginPath();
          ctx.arc(0, 0, p.size * 2, 0, Math.PI * 2);
          ctx.fill();
        } else {
          // Cozy study: gentle floating golden dust motes
          ctx.fillStyle = '#E8CFA6';
          ctx.beginPath();
          ctx.arc(0, 0, p.size * 0.6, 0, Math.PI * 2);
          ctx.fill();
        }

        ctx.restore();
      });

      animId = requestAnimationFrame(render);
    };

    render();

    return () => {
      window.removeEventListener('resize', handleResize);
      cancelAnimationFrame(animId);
    };
  }, [mood, enabled]);

  return (
    <canvas
      ref={canvasRef}
      className="pointer-events-none absolute inset-0 h-full w-full z-20"
      aria-hidden="true"
    />
  );
};
