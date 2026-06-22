# Upgrading

## 1.x → 2.0

One breaking change: **validator constructors take only validator-specific arguments**.
The failure message, translation replacements and locale moved from constructor
arguments to chainable methods on every validator.

### What changed

In 1.x the message/replace/locale were the trailing constructor arguments, so their
position differed between validators (after the domain args, if any). In 2.0 they are
fluent and uniform across every validator, and the message resolves lazily at
validation time.

```php
// 1.x
new TextFieldValidator('Bad input');
new StringFieldValidator(0, 64, 'Too long');
new RadioFieldValidator(['a', 'b'], 'Pick one');
new DateFieldValidator(null, 'Invalid date');
new LaravelRule(['email'], [], 'Bad address');
new EmailFieldValidator('x', ['attr' => 'email'], 'fr'); // replace + locale

// 2.0
new TextFieldValidator()->errorMessage('Bad input');
new StringFieldValidator(0, 64)->errorMessage('Too long');
new RadioFieldValidator(['a', 'b'])->errorMessage('Pick one');
new DateFieldValidator()->errorMessage('Invalid date');
new LaravelRule(['email'])->errorMessage('Bad address');     // per-rule messages still: new LaravelRule(['email'], ['email' => '…'])
new EmailFieldValidator()->replace(['attr' => 'email'])->locale('fr');
```

The domain arguments are unchanged and stay in the same positions:
`StringFieldValidator(int $minLength = 0, int $maxLength = 255)`,
`DateFieldValidator(?array $formats = null)`, `RadioFieldValidator(array $options)`,
`SelectFieldValidator(array $options)`, `UuidOrIntegerOrSlugValidator(string $uuidVersion = 'uuid')`,
`LaravelRule(array|string $rules, array $messages = [])`.

### How to migrate

For each validator construction that passed a custom message, drop the trailing
message/replace/locale arguments and chain the equivalents:

- `errorMessage` argument → `->errorMessage(…)`
- `replace` argument → `->replace([…])`
- `locale` argument → `->locale(…)`
- `LaravelRule(..., explicitMessage: 'x')` → `new LaravelRule(...)->errorMessage('x')`

A quick way to find the call sites:

```bash
grep -rnE "new [A-Za-z]+Validator\([^)]" your-app/
```

Validators constructed **without** a custom message (the common case, including
auto-resolved form-field defaults) need no change — they already used the translated
default message.

### Notes

- Custom validators that extend `AbstractValidator` inherit `errorMessage()`,
  `replace()` and `locale()` for free, and should return `$this->resolvedMessage()`
  from `validate()` (instead of the old `$this->errorMessage` property).
- Validators implementing `ValidatorInterface` directly are unaffected.
- The message is now resolved at validation time, so it reflects the locale active
  when the value is validated (not when the validator was constructed).

---

[← Docs index](README.md#documentation)
