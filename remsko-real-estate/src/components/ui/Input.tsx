import React from 'react';

interface InputProps {
  label?: string;
  type?: string;
  placeholder?: string;
  value: string;
  onChange: (e: React.ChangeEvent<HTMLInputElement>) => void;
  error?: string;
  disabled?: boolean;
  className?: string;
}

const Input: React.FC<InputProps> = ({
  label,
  type = 'text',
  placeholder,
  value,
  onChange,
  error,
  disabled = false,
  className = '',
}) => {
  return (
    <div className="space-y-1">
      {label && (
        <label htmlFor={label} className="block text-sm font-medium text-gray-700">
          {label}
        </label>
      )}
      <input
        type={type}
        id={label}
        value={value}
        onChange={onChange}
        placeholder={placeholder}
        disabled={disabled}
        className={`
          w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-gray-800
          focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500
          disabled:cursor-not-allowed disabled:bg-gray-100 disabled:opacity-70
          transition-colors
          ${error ? 'border-red-500 focus:border-red-500 focus:ring-red-500' : ''}
          ${className}
        `}
      />
      {error && <p className="text-red-600 text-sm mt-1">{error}</p>}
    </div>
  );
};

export default Input;