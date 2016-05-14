#
# This file is part of the Alice package.
#
#  (c) Nelmio <hello@nelm.io>
#
#  For the full copyright and license information, please view the LICENSE
#  file that was distributed with this source code.
#

# Since using php in this way is generally bad practice, it's not a huge
# problem, but the first newline following ?> is removed, and thus requires
# a second newline for php echo statement of this type

contextual: <?php echo $context['value']; ?>

username: <username()>
