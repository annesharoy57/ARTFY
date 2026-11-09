
**Artfy – Art Gallery Management System**

Artfy is a web-based platform that connects artists with art lovers. It allows artists to showcase their artworks, and participate in online exhibitions. Users can explore artworks, discover artists, search and filter by preferences, like artworks, and save favorites. Built with Laravel, PHP, and MySQL, Artfy aims to make art discovery simple and accessible.

Online exhibitions support up to three admin-created co-admin accounts. Co-admins score submitted art from 1–10 during the visiting period; art lovers can vote once per artwork during that period and purchase exhibition art through the existing SSLCOMMERZ checkout. Final points are the total of votes and co-admin scores. When an exhibition closes, awards are settled automatically: 20% of verified fees is credited to the Artfy commission ledger and the 1st, 2nd, and 3rd allocations are 40%, 30%, and 10%. Tied artists split the combined allocations for the tied places. Prize credits are recorded in each artist's exhibition wallet; amounts for unfilled places are tracked as unallocated.

The Laravel scheduler runs the `exhibitions:settle-closed` command every minute. Keep `php artisan schedule:work` running, or configure Windows Task Scheduler to run `php artisan schedule:run` every minute, for settlement without page traffic. Visiting an exhibition, admin exhibition page, co-admin exhibition page, or artist wallet also settles any eligible closed exhibitions.
