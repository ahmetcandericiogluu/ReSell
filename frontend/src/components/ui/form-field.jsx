/**
 * FormField Component
 * 
 * Wrapper component for form inputs that includes label and error message.
 * Promotes consistent form field styling across the app.
 * 
 * @example
 * <FormField 
 *   label="Email" 
 *   error={errors.email}
 *   required
 * >
 *   <Input type="email" {...register('email')} />
 * </FormField>
 */

const FormField = ({ 
  label,
  error,
  required = false,
  hint,
  children,
  className = '',
  ...props 
}) => {
  return (
    <div className={`space-y-1.5 ${className}`} {...props}>
      {label && (
        <label className="block text-sm font-medium text-slate-700">
          {label}
          {required && <span className="text-red-500 ml-1">*</span>}
          {hint && <span className="text-slate-500 text-xs ml-2">({hint})</span>}
        </label>
      )}
      
      {children}
      
      {error && (
        <p className="text-xs text-red-600 mt-1">
          {error}
        </p>
      )}
    </div>
  );
};

export default FormField;

