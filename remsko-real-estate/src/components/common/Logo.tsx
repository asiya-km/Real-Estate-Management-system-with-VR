import React from 'react';
import { Building2, HomeIcon } from 'lucide-react';

interface LogoProps {
  size?: 'sm' | 'md' | 'lg';
  variant?: 'default' | 'light' | 'dark';
}

const Logo: React.FC<LogoProps> = ({ size = 'md', variant = 'default' }) => {
  const sizeConfig = {
    sm: { icon: 28, wrapper: 'w-14 h-14' },
    md: { icon: 40, wrapper: 'w-20 h-20' },
    lg: { icon: 56, wrapper: 'w-28 h-28' },
  };
  
  const colorConfig = {
    default: { primary: 'text-emerald-600', secondary: 'text-blue-800', bg: 'bg-white' },
    light: { primary: 'text-emerald-400', secondary: 'text-blue-100', bg: 'bg-blue-900/30' },
    dark: { primary: 'text-emerald-600', secondary: 'text-blue-950', bg: 'bg-gray-100' },
  };
  
  const { icon, wrapper } = sizeConfig[size];
  const { primary, secondary, bg } = colorConfig[variant];
  
  return (
    <div className={`${wrapper} mx-auto rounded-full ${bg} flex items-center justify-center shadow-lg relative overflow-hidden`}>
      <div className="absolute inset-0 bg-gradient-to-br from-blue-500/10 to-emerald-500/10"></div>
      <Building2 size={icon} className={`${primary} transform transition-all duration-500 hover:scale-110`} />
    </div>
  );
};

export default Logo;