<?php

declare(strict_types=1);

return [

    // Interaction
    'non_interactive_required' => "A value for ':label' is required, but the console is running non-interactively. Provide it via a command option or environment variable.",
    'invalid_input'            => 'Invalid input. Please try again.',

    // Errors
    'render_failed' => "The :widget widget could not render: :reason",
    'invalid_color' => "':value' is not a valid hex colour or known colour name.",

    // Status labels
    'status' => [
        'success' => 'Success',
        'error'   => 'Error',
        'warning' => 'Warning',
        'info'    => 'Info',
        'pending' => 'Pending',
    ],

];
