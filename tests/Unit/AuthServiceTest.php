<?php

namespace Tests\Unit\Services;

use Tests\TestCase;
use Mockery;
use Mockery\MockInterface;
use App\Services\AuthService;
use App\Interfaces\UserRepositoryInterface;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthServiceTest extends TestCase
{
    /** @var UserRepositoryInterface&MockInterface */
    private UserRepositoryInterface $userRepo;
    private AuthService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->userRepo = Mockery::mock(UserRepositoryInterface::class);

        $this->service = new AuthService($this->userRepo);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    // -------------------------------------------------------------------------
    // TEST 1: Login berhasil dengan kredensial yang valid
    // -------------------------------------------------------------------------
    /** @test */
    public function login_berhasil_dengan_kredensial_valid(): void
    {
        // Arrange — siapkan user palsu dengan password yang sudah di-hash
        $user = Mockery::mock(User::class)->makePartial();
        $user->id        = 1;
        $user->name      = 'Admin Gudang';
        $user->email     = 'admin@iscm.test';
        $user->password  = Hash::make('password');
        $user->is_active = true;

        // userRepo->findByEmail() akan return user palsu
        $this->userRepo
            ->shouldReceive('findByEmail')
            ->once()
            ->with('admin@iscm.test')
            ->andReturn($user);

        // User->load('roles') dipanggil untuk resource
        $user->shouldReceive('load')
            ->once()
            ->with('roles')
            ->andReturnSelf();

        // Mock JWTAuth::fromUser() agar tidak butuh koneksi nyata
        JWTAuth::shouldReceive('fromUser')
            ->once()
            ->with($user)
            ->andReturn('mocked.jwt.token');

        // Mock config jwt.ttl
        config(['jwt.ttl' => 60]);

        // Act
        $result = $this->service->login([
            'email'    => 'admin@iscm.test',
            'password' => 'password',
        ]);

        // Assert
        $this->assertArrayHasKey('token', $result);
        $this->assertEquals('mocked.jwt.token', $result['token']);
        $this->assertEquals('bearer', $result['token_type']);
        $this->assertEquals(3600, $result['expires_in']);
    }

    // -------------------------------------------------------------------------
    // TEST 2: Login gagal — password salah
    // -------------------------------------------------------------------------
    /** @test */
    public function login_gagal_jika_password_salah(): void
    {
        // Arrange
        $user = Mockery::mock(User::class)->makePartial();
        $user->password = Hash::make('password_benar');

        $this->userRepo
            ->shouldReceive('findByEmail')
            ->once()
            ->with('admin@iscm.test')
            ->andReturn($user);

        // Assert — ValidationException harus dilempar
        $this->expectException(ValidationException::class);

        // Act
        $this->service->login([
            'email'    => 'admin@iscm.test',
            'password' => 'password_salah', // ← salah
        ]);
    }

    // -------------------------------------------------------------------------
    // TEST 3: Login gagal — akun dinonaktifkan
    // -------------------------------------------------------------------------
    /** @test */
    public function login_gagal_jika_akun_nonaktif(): void
    {
        // Arrange — user ada, password benar, tapi is_active = false
        $user = Mockery::mock(User::class)->makePartial();
        $user->password  = Hash::make('password');
        $user->is_active = false; // ← nonaktif

        $this->userRepo
            ->shouldReceive('findByEmail')
            ->once()
            ->with('admin@iscm.test')
            ->andReturn($user);

        // Assert
        $this->expectException(ValidationException::class);

        // Act
        $this->service->login([
            'email'    => 'admin@iscm.test',
            'password' => 'password',
        ]);
    }

    // -------------------------------------------------------------------------
    // TEST 4: Login gagal — email tidak terdaftar
    // -------------------------------------------------------------------------
    /** @test */
    public function login_gagal_jika_email_tidak_ditemukan(): void
    {
        // Arrange — repository return null (user tidak ada)
        $this->userRepo
            ->shouldReceive('findByEmail')
            ->once()
            ->with('tidakada@iscm.test')
            ->andReturn(null);

        // Assert
        $this->expectException(ValidationException::class);

        // Act
        $this->service->login([
            'email'    => 'tidakada@iscm.test',
            'password' => 'password',
        ]);
    }

    // -------------------------------------------------------------------------
    // TEST 5: Logout berhasil — token diinvalidate
    // -------------------------------------------------------------------------
    /** @test */
    public function logout_berhasil_menginvalidate_token(): void
    {
        // Arrange — mock JWTAuth chain
        $mockToken = Mockery::mock(\Tymon\JWTAuth\Token::class);

        JWTAuth::shouldReceive('getToken')
            ->once()
            ->andReturn($mockToken);

        JWTAuth::shouldReceive('invalidate')
            ->once()
            ->with($mockToken)
            ->andReturn(true);

        // Act & Assert — tidak boleh throw exception
        $this->service->logout();

        // Kalau sampai sini tanpa exception = logout berhasil
        $this->assertTrue(true);
    }
}
