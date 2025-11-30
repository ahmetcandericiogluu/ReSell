/**
 * Badge Component
 * 
 * Small label/tag component for status, categories, etc.
 * 
 * @example
 * <Badge variant="success">Active</Badge>
 * <Badge variant="warning">Draft</Badge>
 */

const Badge = ({ 
  children, 
  variant = 'default',
  className = '',
  ...props 
}) => {
  const baseStyles = 'inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium';
  
  const variants = {
    default: 'bg-slate-100 text-slate-700',
    primary: 'bg-primary-50 text-primary-700',
    success: 'bg-emerald-50 text-emerald-700',
    warning: 'bg-amber-50 text-amber-700',
    danger: 'bg-red-50 text-red-700',
  };
  
  const variantClass = variants[variant] || variants.default;
  
  return (
    <span 
      className={`${baseStyles} ${variantClass} ${className}`}
      {...props}
    >
      {children}
    </span>
  );
};

export default Badge;

