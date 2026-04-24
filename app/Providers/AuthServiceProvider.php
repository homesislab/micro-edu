use App\Models\Team;
use App\Policies\TeamPolicy;
use App\Models\Enrollment;
use App\Policies\EnrollmentPolicy;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        Enrollment::class => EnrollmentPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot()
    {
        parent::boot();
    }
}