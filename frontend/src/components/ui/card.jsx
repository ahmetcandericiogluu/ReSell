/**
 * Card Component
 * 
 * Reusable container with consistent styling.
 * Used for form containers, content sections, listings, etc.
 * 
 * @example
 * <Card>
 *   <h2>Title</h2>
 *   <p>Content goes here</p>
 * </Card>
 * 
 * <Card variant="bordered" padding="lg">
 *   ...
 * </Card>
 */

const Card = ({ 
  children, 
  variant = 'default',
  padding = 'md',
  className = '',
  ...props 
}) => {
  const baseStyles = 'bg-white rounded-xl';
  
  const variants = {
    default: 'shadow-sm border border-slate-200',
    bordered: 'border border-slate-200',
    elevated: 'shadow-md',
  };
  
  const paddings = {
    none: '',
    sm: 'p-4',
    md: 'p-6',
    lg: 'p-8',
  };
  
  const variantClass = variants[variant] || variants.default;
  const paddingClass = paddings[padding] || paddings.md;
  
  return (
    <div 
      className={`${baseStyles} ${variantClass} ${paddingClass} ${className}`}
      {...props}
    >
      {children}
    </div>
  );
};

export default Card;

