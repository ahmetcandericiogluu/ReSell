/**
 * Button Component
 * 
 * Reusable button with multiple variants and sizes.
 * Uses Tailwind utilities for consistent styling across the app.
 * 
 * @example
 * <Button variant="primary" onClick={handleClick}>Save</Button>
 * <Button variant="secondary" size="sm">Cancel</Button>
 */

const Button = ({ 
  children, 
  variant = 'primary', 
  size = 'md', 
  className = '', 
  disabled = false,
  type = 'button',
  ...props 
}) => {
  // Base styles applied to all buttons
  const baseStyles = 'inline-flex items-center justify-center font-medium rounded-lg transition-colors focus:outline-none focus:ring-2 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed';
  
  // Variant styles
  const variants = {
    primary: 'bg-primary-600 text-white hover:bg-primary-700 focus:ring-primary-500',
    secondary: 'bg-slate-100 text-slate-700 hover:bg-slate-200 focus:ring-slate-500 border border-slate-200',
    ghost: 'text-slate-600 hover:bg-slate-50 hover:text-slate-900 focus:ring-slate-500',
    danger: 'bg-red-50 text-red-700 hover:bg-red-100 focus:ring-red-500 border border-red-200',
  };
  
  // Size styles
  const sizes = {
    sm: 'px-3 py-1.5 text-sm',
    md: 'px-4 py-2.5 text-sm',
    lg: 'px-6 py-3 text-base',
  };
  
  const variantClass = variants[variant] || variants.primary;
  const sizeClass = sizes[size] || sizes.md;
  
  return (
    <button
      type={type}
      disabled={disabled}
      className={`${baseStyles} ${variantClass} ${sizeClass} ${className}`}
      {...props}
    >
      {children}
    </button>
  );
};

export default Button;

